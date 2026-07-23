<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('exam_center_id')->nullable()->constrained('exam_centers')->nullOnDelete();

            $table->enum('class_level', ['ssc_part1', 'ssc_part2', 'hsc_part1', 'hsc_part2']);
            $table->string('subject_name', 150);
            $table->string('subject_code', 20);
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('total_marks')->default(100);

            $table->timestamps();

            $table->index(['academic_year_id', 'class_level', 'exam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_timetables');
    }
};
