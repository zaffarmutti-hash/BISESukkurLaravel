<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('window_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();

            // What level this override applies to
            $table->enum('scope_type', ['district', 'school']);
            $table->unsignedBigInteger('scope_id'); // ID of the district or school

            // Which window this override covers
            $table->enum('window_type', ['enrollment', 'examination']);

            // Phase boundary dates — fully replace the global dates for this scope
            $table->datetime('normal_start');
            $table->datetime('normal_end');
            $table->datetime('grace_end')->nullable(); // null = no grace period

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Only one override per scope per window per year
            $table->unique(
                ['academic_year_id', 'scope_type', 'scope_id', 'window_type'],
                'window_override_unique'
            );

            $table->index(['scope_type', 'scope_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('window_overrides');
    }
};
