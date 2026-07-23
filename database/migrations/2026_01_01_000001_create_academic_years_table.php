<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('label', 20)->unique();          // e.g. "2025-2026"
            $table->smallInteger('year_start');              // e.g. 2025
            $table->smallInteger('year_end');                // e.g. 2026
            $table->boolean('is_active')->default(false);   // Only one row ever true

            // Enrollment window
            $table->date('enrollment_open_date')->nullable();
            $table->date('enrollment_close_date')->nullable();
            $table->boolean('enrollment_window_open')->default(false);

            // Examination window
            $table->date('examination_open_date')->nullable();
            $table->date('examination_close_date')->nullable();
            $table->boolean('examination_window_open')->default(false);

            $table->timestamps();

            // Constraint: only one active year
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
