<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('code', 20)->unique();       // Official school/college code
            $table->enum('type', ['school', 'college'])->default('school');
            $table->enum('gender', ['male', 'female', 'mixed'])->default('mixed');
            $table->string('principal_name', 150)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['district_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
