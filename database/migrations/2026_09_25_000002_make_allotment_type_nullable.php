<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make allotment_type nullable
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE students ALTER COLUMN allotment_type DROP NOT NULL');
        } elseif (DB::getDriverName() !== 'sqlite') {
            Schema::table('students', function (Blueprint $table) {
                $table->string('allotment_type', 20)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE students SET allotment_type = 'auto' WHERE allotment_type IS NULL");
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE students ALTER COLUMN allotment_type SET NOT NULL');
        } elseif (DB::getDriverName() !== 'sqlite') {
            Schema::table('students', function (Blueprint $table) {
                $table->string('allotment_type', 20)->nullable(false)->default('auto')->change();
            });
        }
    }
};
