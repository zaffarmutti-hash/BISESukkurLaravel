<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_form_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_form_id')->constrained('exam_forms')->cascadeOnDelete();
            $table->foreignId('exam_timetable_id')->constrained('exam_timetables')->restrictOnDelete();

            // Subject details snapshot at time of form submission
            $table->string('subject_name', 150);
            $table->string('subject_code', 20);
            $table->boolean('is_elective')->default(false);

            $table->timestamps();

            $table->unique(['exam_form_id', 'exam_timetable_id'], 'exam_form_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_form_subjects');
    }
};
