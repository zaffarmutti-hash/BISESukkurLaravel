<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Atomic sequence generator for invoice numbers per year/type
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            // TODO: Add columns in Phase 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
