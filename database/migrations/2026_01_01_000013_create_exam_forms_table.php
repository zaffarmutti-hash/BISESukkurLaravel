<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('student_academic_record_id')->constrained('student_academic_records')->restrictOnDelete();
            $table->foreignId('exam_center_id')->nullable()->constrained('exam_centers')->nullOnDelete();

            // RULE: A student MUST have an enrollment_number before an exam form can exist
            // Enforced at application level in ExamFormService and database level via student FK

            // Seat number assigned by Super Admin after exam challan is confirmed
            $table->string('seat_number', 20)->nullable()->unique();
            $table->timestamp('seat_number_assigned_at')->nullable();

            // Admission slip generated flag
            $table->boolean('admission_slip_generated')->default(false);
            $table->timestamp('admission_slip_generated_at')->nullable();

            $table->enum('status', ['draft', 'submitted', 'confirmed', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // One exam form per student per year
            $table->unique(['student_id', 'academic_year_id'], 'exam_form_student_year_unique');
            $table->index(['academic_year_id', 'exam_center_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_forms');
    }
};
