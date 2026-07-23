<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('granted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();

            // What kind of exception is being granted
            $table->enum('exception_type', [
                'allow_record_edit_after_lock',      // Edit a locked student record
                'extend_enrollment_deadline',        // Extra days past enrollment close
                'extend_examination_deadline',       // Extra days past exam close
                'allow_challan_resubmission',        // Resubmit a rejected challan
                'other',
            ]);

            // Mandatory written reason
            $table->text('reason');

            // Optional: exception expires on a date
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Optional: narrow to a specific student
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['school_id', 'is_active', 'exception_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_exceptions');
    }
};
