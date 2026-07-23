<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Audit trail of all changes to student records
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activity_logs', function (Blueprint $table) {
            $table->id();
            // TODO: Add columns in Phase 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activity_logs');
    }
};
