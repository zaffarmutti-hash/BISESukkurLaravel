<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AcademicYear;
use App\Models\District;
use App\Models\School;
use App\Models\WindowOverride;
use App\Services\WindowPhaseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WindowPhaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private WindowPhaseService $service;
    private AcademicYear $year;
    private District $district;
    private School $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WindowPhaseService();

        // Set up base entities
        $this->year = AcademicYear::create([
            'label' => '2026-2027',
            'year_start' => 2026,
            'year_end' => 2027,
            'is_active' => true,
            'enrollment_window_open' => true,
            'examination_window_open' => true,
            'enrollment_open_date' => Carbon::now()->subDays(5)->toDateString(),
            'enrollment_close_date' => Carbon::now()->addDays(5)->toDateString(),
            'enrollment_grace_end' => Carbon::now()->addDays(10),
            'examination_open_date' => Carbon::now()->subDays(5)->toDateString(),
            'examination_close_date' => Carbon::now()->addDays(5)->toDateString(),
            'examination_grace_end' => Carbon::now()->addDays(10),
        ]);

        $this->district = District::create(['name' => 'Sukkur Test District', 'code' => 'SUK']);
        $this->school = School::create([
            'name' => 'Test High School',
            'username' => 'testschool',
            'code' => 'SCH001',
            'type' => 'government',
            'district_id' => $this->district->id,
            'is_active' => true,
        ]);
    }

    public function test_resolves_normal_phase_correctly()
    {
        $result = $this->service->resolvePhase($this->school->id, 'enrollment');
        
        $this->assertEquals('normal', $result->phase);
        $this->assertTrue($result->isAccessAllowed);
        $this->assertEquals('global', $result->overrideLevel);
    }

    public function test_resolves_grace_phase_correctly()
    {
        // Set dates so current time is in grace period
        $this->year->update([
            'enrollment_open_date' => Carbon::now()->subDays(10)->toDateString(),
            'enrollment_close_date' => Carbon::now()->subDays(2)->toDateString(),
            'enrollment_grace_end' => Carbon::now()->addDays(3),
        ]);

        $result = $this->service->resolvePhase($this->school->id, 'enrollment');
        
        $this->assertEquals('grace', $result->phase);
        $this->assertTrue($result->isAccessAllowed);
    }

    public function test_resolves_closed_phase_correctly()
    {
        // Set dates in the past
        $this->year->update([
            'enrollment_open_date' => Carbon::now()->subDays(15)->toDateString(),
            'enrollment_close_date' => Carbon::now()->subDays(10)->toDateString(),
            'enrollment_grace_end' => Carbon::now()->subDays(5),
        ]);

        $result = $this->service->resolvePhase($this->school->id, 'enrollment');
        
        $this->assertEquals('closed', $result->phase);
        $this->assertFalse($result->isAccessAllowed);
    }

    public function test_district_override_precedence()
    {
        // Set global to closed
        $this->year->update([
            'enrollment_open_date' => Carbon::now()->subDays(15)->toDateString(),
            'enrollment_close_date' => Carbon::now()->subDays(10)->toDateString(),
            'enrollment_grace_end' => Carbon::now()->subDays(5),
        ]);

        // Add a district override for normal phase
        WindowOverride::create([
            'academic_year_id' => $this->year->id,
            'scope_type' => 'district',
            'scope_id' => $this->district->id,
            'window_type' => 'enrollment',
            'normal_start' => Carbon::now()->subDays(2),
            'normal_end' => Carbon::now()->addDays(2),
            'grace_end' => Carbon::now()->addDays(5),
            'is_active' => true,
        ]);

        $result = $this->service->resolvePhase($this->school->id, 'enrollment');
        
        $this->assertEquals('normal', $result->phase);
        $this->assertTrue($result->isAccessAllowed);
        $this->assertEquals('district', $result->overrideLevel);
    }

    public function test_school_override_precedence_over_district()
    {
        // Set global to closed
        $this->year->update([
            'enrollment_open_date' => Carbon::now()->subDays(15)->toDateString(),
            'enrollment_close_date' => Carbon::now()->subDays(10)->toDateString(),
            'enrollment_grace_end' => Carbon::now()->subDays(5),
        ]);

        // Add district override (closed)
        WindowOverride::create([
            'academic_year_id' => $this->year->id,
            'scope_type' => 'district',
            'scope_id' => $this->district->id,
            'window_type' => 'enrollment',
            'normal_start' => Carbon::now()->subDays(10),
            'normal_end' => Carbon::now()->subDays(5),
            'grace_end' => Carbon::now()->subDays(2),
            'is_active' => true,
        ]);

        // Add school override (open)
        WindowOverride::create([
            'academic_year_id' => $this->year->id,
            'scope_type' => 'school',
            'scope_id' => $this->school->id,
            'window_type' => 'enrollment',
            'normal_start' => Carbon::now()->subDays(2),
            'normal_end' => Carbon::now()->addDays(2),
            'grace_end' => Carbon::now()->addDays(5),
            'is_active' => true,
        ]);

        $result = $this->service->resolvePhase($this->school->id, 'enrollment');
        
        $this->assertEquals('normal', $result->phase);
        $this->assertTrue($result->isAccessAllowed);
        $this->assertEquals('school', $result->overrideLevel);
    }

    public function test_master_kill_switch()
    {
        $this->year->update(['enrollment_window_open' => false]);

        $result = $this->service->resolvePhase($this->school->id, 'enrollment');
        
        $this->assertEquals('closed', $result->phase);
        $this->assertFalse($result->isAccessAllowed);
    }
}
