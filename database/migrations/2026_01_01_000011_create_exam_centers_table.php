<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('code', 20)->unique();
            $table->string('address', 500)->nullable();
            $table->unsignedInteger('capacity')->default(0);   // Max seats
            $table->string('invigilator_name', 150)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['district_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_centers');
    }
};
