<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\District;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PureBladeMigrationTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure active academic year exists
        AcademicYear::create([
            'label' => '2026-2027',
            'year_start' => 2026,
            'year_end' => 2027,
            'is_active' => true,
            'enrollment_window_open' => true,
            'examination_window_open' => true,
        ]);

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_public_routes_return_blade_views(): void
    {
        $loginRes = $this->get('/login');
        $loginRes->assertOk();

        $formRes = $this->get('/enrollment-form');
        $formRes->assertOk();

        $rootRes = $this->get('/');
        $rootRes->assertRedirect(route('login'));
    }

    public function test_district_routes_return_blade_views(): void
    {
        $district = District::firstOrCreate(
            ['code' => 'SUK'],
            ['name' => 'Sukkur']
        );

        $districtAdmin = User::firstOrCreate(
            ['username' => 'test_district_admin'],
            [
                'name' => 'Test District Admin',
                'email' => 'test_district@test.com',
                'password' => Hash::make('password123'),
                'role' => 'district_admin',
                'district_id' => $district->id,
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $districtAdmin->must_change_password = false;
        $districtAdmin->save();

        Role::firstOrCreate(['name' => 'district_admin', 'guard_name' => 'web']);
        $districtAdmin->assignRole('district_admin');

        $this->actingAs($districtAdmin)->get('/district/schools')->assertOk();
        $this->actingAs($districtAdmin)->get('/district/reports')->assertOk();
        $this->actingAs($districtAdmin)->get('/district/announcements')->assertOk();
    }

    public function test_superadmin_routes_return_blade_views_or_redirects(): void
    {
        $superAdmin = User::firstOrCreate(
            ['username' => 'test_superadmin'],
            [
                'name' => 'Test Super Admin',
                'email' => 'test_superadmin@test.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $superAdmin->must_change_password = false;
        $superAdmin->save();

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $permissions = [
            'report.enrollment', 'settings.system_settings', 'settings.notifications',
            'academicyear.manage_windows', 'academicyear.create_next', 'feerate.view'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role->syncPermissions(Permission::all());
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)->get('/superadmin/reports-analytics')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/special-permissions')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/settings/notifications')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/window-overrides')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/academic-year-transition')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/fee-rates')->assertOk();
    }

    public function test_admin_module_routes_redirect_to_superadmin_blade_routes(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::where('role', 'super_admin')->first();
        if (!$superAdmin) {
            $superAdmin = User::create([
                'username' => 'test_superadmin_admin_mod',
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'must_change_password' => false,
            ]);
            $superAdmin->assignRole('super_admin');
        }

        $this->actingAs($superAdmin)->get('/admin/examination/centers')->assertRedirect(route('superadmin.exam.centers'));
        $this->actingAs($superAdmin)->get('/admin/examination/results')->assertRedirect(route('superadmin.exam.results'));
        $this->actingAs($superAdmin)->get('/admin/examination/timetable')->assertRedirect(route('superadmin.exam.timetable'));
    }

    public function test_school_routes_return_blade_views(): void
    {
        $district = District::firstOrCreate(['code' => 'SUK'], ['name' => 'Sukkur']);
        $school = School::firstOrCreate(
            ['username' => 'TEST-SCH-01'],
            [
                'district_id' => $district->id,
                'name' => 'Test Model School',
                'type' => 'school',
                'gender' => 'mixed',
                'is_active' => true,
            ]
        );

        $schoolAdmin = User::firstOrCreate(
            ['username' => 'test_school_admin_unique'],
            [
                'name' => 'School Admin User',
                'email' => 'unique_school_admin@test.com',
                'password' => Hash::make('password123'),
                'role' => 'school_admin',
                'school_id' => $school->id,
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $schoolAdmin->must_change_password = false;
        $schoolAdmin->school_id = $school->id;
        $schoolAdmin->save();

        Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
        $schoolAdmin->assignRole('school_admin');

        $this->actingAs($schoolAdmin)->get('/school')->assertOk();
        $this->actingAs($schoolAdmin)->get('/school/students')->assertOk();
        $this->actingAs($schoolAdmin)->get('/school/invoices')->assertOk();
    }
}
