<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('exam_form_id')->constrained('exam_forms')->restrictOnDelete();
            $table->foreignId('exam_timetable_id')->constrained('exam_timetables')->restrictOnDelete();

            $table->string('subject_name', 150);
            $table->string('subject_code', 20);

            // Marks
            $table->unsignedSmallInteger('total_marks');
            $table->unsignedSmallInteger('marks_obtained')->nullable();
            $table->decimal('passing_percentage', 5, 2)->default(33.00);  // Minimum to pass

            // Computed fields (calculated by system, not entered manually)
            $table->boolean('is_pass')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 5)->nullable();       // A+, A, B, C, D, F

            $table->enum('status', ['pending', 'pass', 'fail', 'absent', 'withheld', 'cancelled'])
                  ->default('pending');

            // Who entered these marks and when
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'exam_timetable_id'], 'result_unique');
            $table->index(['academic_year_id', 'exam_form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
