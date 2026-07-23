<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── student_academic_records: add status column ─────────────────
        Schema::table('student_academic_records', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->after('student_type');
            $table->index(['academic_year_id', 'status'], 'sar_year_status_index');
        });

        // ─── exam_forms: add 'final' to existing status check constraint ─────
        // Since status is a VARCHAR column with a CHECK constraint in Postgres:
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE exam_forms DROP CONSTRAINT IF EXISTS exam_forms_status_check");
            DB::statement("ALTER TABLE exam_forms ADD CONSTRAINT exam_forms_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'final'::text, 'submitted'::text, 'confirmed'::text, 'rejected'::text]))");
        }
    }

    public function down(): void
    {
        Schema::table('student_academic_records', function (Blueprint $table) {
            $table->dropIndex('sar_year_status_index');
            $table->dropColumn('status');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE exam_forms DROP CONSTRAINT IF EXISTS exam_forms_status_check");
            DB::statement("ALTER TABLE exam_forms ADD CONSTRAINT exam_forms_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'submitted'::text, 'confirmed'::text, 'rejected'::text]))");
        }
    }
};
