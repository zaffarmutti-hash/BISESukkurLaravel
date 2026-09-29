<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            // 'string' | 'boolean' | 'integer' | 'float'
            $table->string('value_type', 20)->default('string');
            $table->string('label', 200)->nullable()->comment('Human-readable label for admin UI');
            $table->text('description')->nullable()->comment('Explains what this setting does');
            $table->timestamps();
        });

        // Seed default values (mirrors former storage/settings.json)
        DB::table('system_settings')->insert([
            [
                'key'        => 'board_name',
                'value'      => 'Board of Intermediate and Secondary Education, Sukkur',
                'value_type' => 'string',
                'label'      => 'Board Name',
                'description'=> 'Official board name printed on all documents.',
            ],
            [
                'key'        => 'contact_email',
                'value'      => 'info@bisesukkur.edu.pk',
                'value_type' => 'string',
                'label'      => 'Contact Email',
                'description'=> 'Official contact email address.',
            ],
            [
                'key'        => 'contact_phone',
                'value'      => '071-9310623',
                'value_type' => 'string',
                'label'      => 'Contact Phone',
                'description'=> 'Official contact phone number.',
            ],
            [
                'key'        => 'late_fee_multiplier',
                'value'      => '1.5',
                'value_type' => 'float',
                'label'      => 'Late Fee Multiplier',
                'description'=> 'Multiplier applied to base fee during grace period (1.5 = 150%).',
            ],
            [
                'key'        => 'grace_period_days',
                'value'      => '15',
                'value_type' => 'integer',
                'label'      => 'Grace Period Days',
                'description'=> 'Default grace days after the enrollment/exam close date.',
            ],
            [
                'key'        => 'passing_percentage',
                'value'      => '33',
                'value_type' => 'float',
                'label'      => 'Passing Percentage',
                'description'=> 'CRITICAL: Minimum marks percentage required to pass. Every change is fully audit-logged.',
            ],
            [
                'key'        => 'enable_email',
                'value'      => '1',
                'value_type' => 'boolean',
                'label'      => 'Enable Email Notifications',
                'description'=> 'Send email alerts for deadlines, verifications, and updates.',
            ],
            [
                'key'        => 'enable_sms',
                'value'      => '0',
                'value_type' => 'boolean',
                'label'      => 'Enable SMS Notifications',
                'description'=> 'Send SMS alerts to school admins on critical events.',
            ],
            [
                'key'        => 'enable_challan_updates',
                'value'      => '1',
                'value_type' => 'boolean',
                'label'      => 'Enable Challan Status Updates',
                'description'=> 'Notify schools when their challan is verified or rejected.',
            ],
            [
                'key'        => 'enable_audit_alerts',
                'value'      => '0',
                'value_type' => 'boolean',
                'label'      => 'Enable Audit Alerts',
                'description'=> 'Email all super admins when a critical setting changes.',
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
