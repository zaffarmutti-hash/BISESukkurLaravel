<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            // Grace period end dates — nullable means "no grace period configured"
            $table->datetime('enrollment_grace_end')->nullable()->after('enrollment_close_date');
            $table->datetime('examination_grace_end')->nullable()->after('examination_close_date');
        });
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn(['enrollment_grace_end', 'examination_grace_end']);
        });
    }
};
