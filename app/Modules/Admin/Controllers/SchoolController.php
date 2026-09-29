<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\InvoiceSequence;
use App\Models\School;
use App\Models\Tehsil;
use App\Models\User;
use App\Services\WindowPhaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SchoolController extends Controller
{
    public function __construct(private ?WindowPhaseService $windowPhaseService = null)
    {
        $this->windowPhaseService = $windowPhaseService ?? app(WindowPhaseService::class);
    }

    /**
     * Display paginated list of schools with comprehensive filters.
     */
    public function index(Request $request)
    {
        $query = School::with(['district', 'tehsil', 'adminUser']);

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        if ($request->filled('school_type')) {
            $query->where('type', $request->input('school_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%")
                  ->orWhere('semis_code', 'ilike', "%{$search}%")
                  ->orWhere('principal_name', 'ilike', "%{$search}%");
            });
        }

        // Filter by Level (SSC Only, Combined, HSC Only)
        if ($request->filled('level')) {
            $lvl = $request->input('level');
            if ($lvl === 'ssc_only') {
                $query->where(function ($q) {
                    $q->whereJsonContains('allowed_levels', 'ssc_part1')
                      ->whereJsonDoesntContain('allowed_levels', 'hsc_part1');
                });
            } elseif ($lvl === 'hsc_only') {
                $query->where(function ($q) {
                    $q->whereJsonContains('allowed_levels', 'hsc_part1')
                      ->whereJsonDoesntContain('allowed_levels', 'ssc_part1');
                });
            } elseif ($lvl === 'combined') {
                $query->where(function ($q) {
                    $q->whereJsonContains('allowed_levels', 'ssc_part1')
                      ->whereJsonContains('allowed_levels', 'hsc_part1');
                });
            }
        }

        $schools = $query->orderBy('name')->paginate(20)->withQueryString();
        $districts = District::orderBy('name')->get(['id', 'name', 'code']);

        return view('superadmin.schools.index', [
            'schools'   => $schools,
            'districts' => $districts,
            'filters'   => $request->only(['district_id', 'school_type', 'level', 'is_active', 'search']),
        ]);
    }

    /**
     * Show registration form.
     */
    public function create()
    {
        $districts = District::orderBy('name')->get();
        $tehsils = Tehsil::orderBy('name')->get();

        return view('superadmin.schools.create', [
            'districts' => $districts,
            'tehsils'   => $tehsils,
        ]);
    }

    /**
     * Atomically register school and create school admin user credentials.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Section 1: School Information
            'name'           => 'required|string|max:200',
            'semis_code'     => 'required|string|size:8|regex:/^[0-9]{8}$/|unique:schools,semis_code',
            'district_id'    => 'required|exists:districts,id',
            'tehsil'         => 'nullable|string|max:100',
            'school_type'    => 'required|in:public,private,school,college',
            'gender'         => 'required|in:male,female,mixed',
            'address'        => 'nullable|string|max:500',

            // Section 2: Head of School
            'head_name'      => 'required|string|max:150',
            'head_mobile'    => 'required|string|regex:/^03[0-9]{2}-[0-9]{7}$/',
            'head_cnic'      => 'required|string|regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/',
            'head_email'     => 'nullable|email|max:150',

            // Section 3: Class Levels Allowed
            'allowed_levels' => 'required|array|min:1',
            'allowed_levels.*' => 'in:ssc_part1,ssc_part2,hsc_part1,hsc_part2',
        ], [
            'semis_code.size'  => 'SEMIS Code must be exactly 8 digits.',
            'head_mobile.regex' => 'Mobile number format must be 03XX-XXXXXXX.',
            'head_cnic.regex'   => 'CNIC format must be 00000-0000000-0.',
        ]);

        // Enforce dependencies: SSC-II requires SSC-I, HSC-II requires HSC-I
        $levels = $validated['allowed_levels'];
        if (in_array('ssc_part2', $levels) && !in_array('ssc_part1', $levels)) {
            $levels[] = 'ssc_part1';
        }
        if (in_array('hsc_part2', $levels) && !in_array('hsc_part1', $levels)) {
            $levels[] = 'hsc_part1';
        }

        $result = DB::transaction(function () use ($validated, $levels) {
            $district = District::findOrFail($validated['district_id']);
            $distCode = strtoupper(substr($district->code ?? $district->name, 0, 3));

            // Type code: Public = 1, Private = 2
            $typeNormalized = strtolower($validated['school_type']);
            $isPublic = in_array($typeNormalized, ['public', 'school']);
            $typeCode = $isPublic ? '1' : '2';

            $prefix = "{$distCode}{$typeCode}";

            // Scoped sequence generation using database lock
            $seqRow = InvoiceSequence::firstOrCreate(
                [
                    'sequence_type' => 'SchoolUsername',
                    'prefix'        => $prefix,
                ],
                [
                    'next_sequence' => 1,
                    'last_number'   => 0,
                ]
            );

            $lockedSeq = InvoiceSequence::where('id', $seqRow->id)->lockForUpdate()->first();
            $seqNumber = $lockedSeq->next_sequence ?? 1;
            $lockedSeq->update([
                'next_sequence' => $seqNumber + 1,
                'last_number'   => $seqNumber,
            ]);

            $seqPadded = str_pad((string) $seqNumber, 3, '0', STR_PAD_LEFT);
            $generatedUsername = "{$prefix}-{$seqPadded}";

            // Resolve Tehsil if passed
            $tehsilId = null;
            if (!empty($validated['tehsil'])) {
                $tehsil = Tehsil::where('district_id', $district->id)
                    ->where('name', 'ilike', trim($validated['tehsil']))
                    ->first();
                $tehsilId = $tehsil?->id;
            }

            // Create School record
            $school = School::create([
                'district_id'    => $district->id,
                'tehsil_id'      => $tehsilId,
                'name'           => $validated['name'],
                'username'       => $generatedUsername,
                'semis_code'     => $validated['semis_code'],
                'type'           => $isPublic ? 'school' : 'college',
                'gender'         => $validated['gender'],
                'principal_name' => $validated['head_name'],
                'phone'          => $validated['head_mobile'],
                'email'          => $validated['head_email'],
                'address'        => $validated['address'],
                'zone'           => 1,
                'allowed_levels' => array_values(array_unique($levels)),
                'is_active'      => true,
            ]);

            // Create User record: password equals username, must_change_password true
            $password = $generatedUsername;
            $user = User::create([
                'username'             => $generatedUsername,
                'name'                 => $validated['head_name'],
                'email'                => $validated['head_email'],
                'password'             => Hash::make($password),
                'school_id'            => $school->id,
                'role'                 => 'school_admin',
                'is_active'            => true,
                'must_change_password' => true,
            ]);
            $user->assignRole('school_admin');

            if (function_exists('activity')) {
                activity('school')
                    ->causedBy(Auth::user())
                    ->performedOn($school)
                    ->withProperties(['username' => $generatedUsername, 'semis_code' => $school->semis_code])
                    ->log("Registered school {$school->name} ({$generatedUsername})");
            }

            return [
                'school'   => $school,
                'username' => $generatedUsername,
                'password' => $password,
            ];
        });

        return redirect()->route('superadmin.schools.create')->with([
            'registered_success' => true,
            'new_school_id'      => $result['school']->id,
            'new_school_name'    => $result['school']->name,
            'new_username'       => $result['username'],
            'new_password'       => $result['password'],
        ]);
    }

    /**
     * Show comprehensive school detail page.
     */
    public function show(School $school)
    {
        $school->loadMissing(['district', 'tehsil', 'adminUser']);
        $activeYear = AcademicYear::current();

        $yearId = $activeYear?->id;
        $studentCount = $school->students()->count();
        $enrolledCount = $school->students()->whereNotNull('enrollment_number')->count();

        // Effective window phase for this school
        $enrPhase = $this->windowPhaseService->resolvePhase($school->id, 'enrollment');
        $examPhase = $this->windowPhaseService->resolvePhase($school->id, 'examination');

        return view('superadmin.schools.show', [
            'school'         => $school,
            'activeYear'     => $activeYear,
            'studentCount'   => $studentCount,
            'enrolledCount'  => $enrolledCount,
            'enrPhase'       => $enrPhase,
            'examPhase'      => $examPhase,
        ]);
    }

    /**
     * Toggle school active status (AJAX).
     */
    public function toggleActive(School $school)
    {
        $school->is_active = !$school->is_active;
        $school->save();

        if (function_exists('activity')) {
            activity('school')
                ->causedBy(Auth::user())
                ->performedOn($school)
                ->log($school->is_active ? 'School activated' : 'School suspended');
        }

        return response()->json([
            'success'   => true,
            'is_active' => $school->is_active,
            'message'   => "School {$school->name} is now " . ($school->is_active ? 'Active' : 'Suspended'),
        ]);
    }
}
