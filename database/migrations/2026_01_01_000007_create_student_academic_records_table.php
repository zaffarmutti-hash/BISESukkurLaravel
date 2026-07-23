<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();

            // Academic classification for THIS year
            $table->enum('class_level', ['ssc_part1', 'ssc_part2', 'hsc_part1', 'hsc_part2']);
            $table->enum('subject_group', ['science', 'arts', 'commerce', 'general', 'pre_medical', 'pre_engineering']);
            $table->enum('student_type', ['fresh', 'repeater', 'private']);

            // If a student moved up from previous year
            $table->foreignId('previous_record_id')->nullable()->constrained('student_academic_records')->nullOnDelete();

            // Lock flag: once enrollment challan is confirmed, this record is locked
            // Exception granted by Super Admin can temporarily unlock
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            // One academic record per student per year
            $table->unique(['student_id', 'academic_year_id'], 'student_year_unique');
            $table->index(['academic_year_id', 'class_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_records');
    }
};
