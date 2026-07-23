<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates')->cascadeOnDelete();
            $table->foreignId('result_id')->constrained('results')->restrictOnDelete();

            $table->string('subject_name', 150);
            $table->string('subject_code', 20);
            $table->unsignedSmallInteger('total_marks');
            $table->unsignedSmallInteger('marks_obtained');
            $table->string('grade', 5)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_subjects');
    }
};
