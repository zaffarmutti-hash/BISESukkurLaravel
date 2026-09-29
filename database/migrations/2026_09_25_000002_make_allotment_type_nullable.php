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
        DB::statement('ALTER TABLE students ALTER COLUMN allotment_type DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE students SET allotment_type = 'auto' WHERE allotment_type IS NULL");
        DB::statement('ALTER TABLE students ALTER COLUMN allotment_type SET NOT NULL');
    }
};
