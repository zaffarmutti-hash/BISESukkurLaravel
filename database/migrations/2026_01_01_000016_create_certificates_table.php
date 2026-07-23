<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();

            // Level passed: SSC or HSC
            $table->enum('level', ['ssc', 'hsc']);

            // Verification token — scannable in QR code for online verification
            $table->string('verification_token', 64)->unique();
            $table->string('verification_url', 500)->nullable();

            // Summary stats on certificate
            $table->decimal('total_percentage', 5, 2)->nullable();
            $table->string('overall_grade', 5)->nullable();
            $table->string('division', 20)->nullable();  // First, Second, Third

            // Certificate document
            $table->string('document_path', 500)->nullable();

            // Issuance
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->boolean('is_issued')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'academic_year_id', 'level'], 'certificate_unique');
            $table->index('verification_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
