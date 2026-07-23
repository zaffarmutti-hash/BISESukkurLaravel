<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('gr_number', 50)->nullable()->after('school_id');
            $table->date('admission_date')->nullable()->after('gr_number');
            $table->string('father_cnic', 15)->nullable()->after('father_name');
            $table->string('guardian_name', 200)->nullable()->after('guardian_phone');
            $table->string('guardian_cnic', 15)->nullable()->after('guardian_name');
            $table->string('surname', 100)->nullable()->after('full_name');
            $table->string('marks_of_identification', 200)->nullable()->after('date_of_birth');
            $table->string('medium_of_instruction', 50)->nullable()->after('religion');
            $table->string('postal_code', 10)->nullable()->after('address');
            $table->text('remarks')->nullable()->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gr_number',
                'admission_date',
                'father_cnic',
                'guardian_name',
                'guardian_cnic',
                'surname',
                'marks_of_identification',
                'medium_of_instruction',
                'postal_code',
                'remarks',
            ]);
        });
    }
};
