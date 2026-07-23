<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Narrow per-school or per-student exceptions granted by Super Admin
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_permissions', function (Blueprint $table) {
            $table->id();
            // TODO: Add columns in Phase 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_permissions');
    }
};
