<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challan_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challan_id')->constrained('challans')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('student_academic_record_id')->constrained('student_academic_records')->restrictOnDelete();

            // Fee applied to this specific student in this challan
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->restrictOnDelete();
            $table->unsignedInteger('amount_paisas');  // Snapshot of fee at time of challan creation

            $table->timestamps();

            // A student can only appear once per challan
            $table->unique(['challan_id', 'student_id'], 'challan_student_unique');

            // A student cannot be in two enrollment challans for the same year
            // Enforced at application level in ChallanGenerationService
            $table->index(['student_id', 'challan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challan_students');
    }
};
