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

    /**
     * Show edit school form.
     */
    public function edit(School $school)
    {
        $school->loadMissing(['district', 'tehsil', 'adminUser']);
        $districts = District::orderBy('name')->get();
        $tehsils = Tehsil::where('district_id', $school->district_id)->orderBy('name')->get();

        return view('superadmin.schools.edit', [
            'school'    => $school,
            'districts' => $districts,
            'tehsils'   => $tehsils,
        ]);
    }

    /**
     * Update school information and allowed class levels.
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'gender'         => 'required|in:boys,girls,co_education',
            'head_name'      => 'required|string|max:255',
            'head_mobile'    => 'required|string|max:20',
            'head_cnic'      => 'nullable|string|max:20',
            'head_email'     => 'nullable|email|max:255',
            'address'        => 'required|string|max:500',
            'allowed_levels' => 'nullable|array',
            'confirm_levels' => 'nullable|string',
        ]);

        $newLevels = $request->input('allowed_levels', []);
        $currentLevels = is_array($school->allowed_levels) ? $school->allowed_levels : [];
        sort($newLevels);
        sort($currentLevels);

        // If levels changed, require typing CONFIRM exactly
        if ($newLevels !== $currentLevels) {
            if (trim($request->input('confirm_levels', '')) !== 'CONFIRM') {
                return back()->withInput()->withErrors([
                    'confirm_levels' => 'Changing allowed class levels alters student exam eligibility. You must type CONFIRM exactly to apply this change.',
                ]);
            }
        }

        try {
            DB::transaction(function () use ($school, $validated, $newLevels) {
                $oldData = $school->only(['name', 'principal_name', 'phone', 'email', 'allowed_levels']);

                $school->update([
                    'name'           => $validated['name'],
                    'gender'         => $validated['gender'],
                    'principal_name' => $validated['head_name'],
                    'phone'          => $validated['head_mobile'],
                    'email'          => $validated['head_email'],
                    'address'        => $validated['address'],
                    'allowed_levels' => array_values(array_unique($newLevels)),
                ]);

                // Also update admin user name/email if exists
                if ($school->adminUser) {
                    $school->adminUser->update([
                        'name'  => $validated['head_name'],
                        'email' => $validated['head_email'] ?? $school->adminUser->email,
                    ]);
                }

                if (function_exists('activity')) {
                    activity('school')
                        ->causedBy(Auth::user())
                        ->performedOn($school)
                        ->withProperties(['old' => $oldData, 'new' => $school->only(['name', 'principal_name', 'phone', 'email', 'allowed_levels'])])
                        ->log("Updated school {$school->name} ({$school->username})");
                }
            });

            return redirect()->route('superadmin.schools.show', $school->id)->with('success', "School '{$school->name}' updated successfully.");

        } catch (\Throwable $e) {
            Log::error('[SchoolController@update] Error updating school: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update school: ' . $e->getMessage());
        }
    }

    /**
     * Show Bulk Import page.
     */
    public function import(Request $request)
    {
        return view('superadmin.schools.import');
    }

    /**
     * Download CSV template for school bulk import.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bise_schools_import_template.csv"',
        ];

        $columns = ['semis_code', 'school_name', 'district_code', 'type', 'gender', 'head_name', 'head_mobile', 'head_email', 'address', 'levels'];
        $example = ['40102001', 'Government Higher Secondary School Sukkur', 'SUK', 'public', 'boys', 'Ghulam Hussain', '0300-1234567', 'ghss@bisesukkur.edu.pk', 'Military Road, Sukkur', 'ssc_part1,ssc_part2,hsc_part1,hsc_part2'];

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process Bulk Import CSV.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to read the uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file appears to be empty.');
        }

        $districtsByCode = District::all()->keyBy(fn ($d) => strtoupper($d->code));
        $validCount = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) {
                continue;
            }

            // Expected columns: semis_code, school_name, district_code, type, gender, head_name, head_mobile, head_email, address, levels
            $semisCode    = trim($row[0] ?? '');
            $schoolName   = trim($row[1] ?? '');
            $districtCode = strtoupper(trim($row[2] ?? ''));
            $schoolType   = strtolower(trim($row[3] ?? 'public'));
            $gender       = strtolower(trim($row[4] ?? 'co_education'));
            $headName     = trim($row[5] ?? '');
            $headMobile   = trim($row[6] ?? '');
            $headEmail    = trim($row[7] ?? '');
            $address      = trim($row[8] ?? '');
            $levelsRaw    = trim($row[9] ?? 'ssc_part1,ssc_part2');

            // Validation
            if (!preg_match('/^\d{8}$/', $semisCode)) {
                $errors[] = "Row {$rowNum}: SEMIS code must be exactly 8 digits ({$semisCode}).";
                continue;
            }

            if (School::where('semis_code', $semisCode)->exists()) {
                $errors[] = "Row {$rowNum}: SEMIS code {$semisCode} is already registered.";
                continue;
            }

            if (empty($schoolName)) {
                $errors[] = "Row {$rowNum}: School name is required.";
                continue;
            }

            if (!$districtsByCode->has($districtCode)) {
                $errors[] = "Row {$rowNum}: Invalid district code '{$districtCode}'. Valid: SUK, KHP, GHT, NSK, KSR.";
                continue;
            }

            $district = $districtsByCode->get($districtCode);
            $isPublic = in_array($schoolType, ['public', 'school', 'govt', 'government']);
            $levels = array_filter(array_map('trim', explode(',', $levelsRaw)));

            try {
                DB::transaction(function () use ($district, $isPublic, $schoolName, $semisCode, $gender, $headName, $headMobile, $headEmail, $address, $levels) {
                    $prefix = "{$district->code}" . ($isPublic ? '1' : '2');

                    $lockKey = crc32("school_seq_{$prefix}");
                    DB::select("SELECT pg_advisory_xact_lock(?)", [$lockKey]);

                    $sequence = InvoiceSequence::firstOrCreate(
                        ['category' => "school_{$prefix}"],
                        ['last_sequence' => 0]
                    );
                    $sequence->increment('last_sequence');
                    $seqPadded = str_pad($sequence->last_sequence, 3, '0', STR_PAD_LEFT);
                    $generatedUsername = "{$prefix}-{$seqPadded}";

                    $school = School::create([
                        'district_id'    => $district->id,
                        'name'           => $schoolName,
                        'username'       => $generatedUsername,
                        'semis_code'     => $semisCode,
                        'type'           => $isPublic ? 'school' : 'college',
                        'gender'         => in_array($gender, ['boys', 'girls', 'co_education']) ? $gender : 'co_education',
                        'principal_name' => $headName ?: 'Principal',
                        'phone'          => $headMobile ?: '0300-0000000',
                        'email'          => $headEmail ?: null,
                        'address'        => $address ?: 'Sindh, Pakistan',
                        'zone'           => 1,
                        'allowed_levels' => array_values(array_unique($levels)),
                        'is_active'      => true,
                    ]);

                    $password = $generatedUsername;
                    $user = User::create([
                        'username'             => $generatedUsername,
                        'name'                 => $headName ?: $schoolName,
                        'email'                => $headEmail ?: null,
                        'password'             => Hash::make($password),
                        'school_id'            => $school->id,
                        'role'                 => 'school_admin',
                        'is_active'            => true,
                        'must_change_password' => true,
                    ]);
                    $user->assignRole('school_admin');
                });

                $validCount++;
            } catch (\Throwable $e) {
                Log::error("[SchoolController@processImport] Row {$rowNum} failed: " . $e->getMessage());
                $errors[] = "Row {$rowNum}: Database error — {$e->getMessage()}";
            }
        }

        fclose($handle);

        return back()->with([
            'import_completed' => true,
            'valid_count'      => $validCount,
            'errors'           => $errors,
        ]);
    }
}
