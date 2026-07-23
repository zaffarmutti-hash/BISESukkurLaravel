<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 200);
            $table->text('body');
            $table->enum('type', ['info', 'warning', 'success'])->default('info');
            $table->enum('target_scope', ['all', 'district', 'school'])->default('all');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->timestamps();

            $table->index(['target_scope', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
