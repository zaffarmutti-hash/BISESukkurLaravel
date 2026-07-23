<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();

            // Who this fee applies to
            $table->enum('class_level', ['ssc_part1', 'ssc_part2', 'hsc_part1', 'hsc_part2']);
            $table->enum('student_type', ['fresh', 'repeater', 'private']);
            $table->enum('fee_type', ['enrollment', 'examination']);   // These are ALWAYS separate

            // Amount in PKR (stored as integer paise to avoid float errors)
            $table->unsignedInteger('amount_paisas');    // e.g. 150000 = PKR 1,500.00

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // A school cannot have two fee amounts for the same combination
            $table->unique(['academic_year_id', 'class_level', 'student_type', 'fee_type'], 'fee_structure_unique');
            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
