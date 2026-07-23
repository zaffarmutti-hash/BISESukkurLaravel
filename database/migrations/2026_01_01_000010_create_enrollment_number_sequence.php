<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates a PostgreSQL named sequence for enrollment numbers.
 *
 * CRITICAL: This is NOT a table. It is a PostgreSQL SEQUENCE object.
 * Using nextval() inside a transaction guarantees that even 1000 concurrent
 * challan confirmations at the exact same millisecond will each receive
 * a unique, non-duplicated enrollment number. No application-level locking
 * is required.
 *
 * The sequence starts at 100001 and increments by 1, NOCYCLE.
 * Once a number is issued via nextval(), it is permanently reserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $start = (int) env('ENROLLMENT_SEQ_START', 100001);

        DB::statement("
            CREATE SEQUENCE IF NOT EXISTS enrollment_number_seq
                START WITH {$start}
                INCREMENT BY 1
                MINVALUE {$start}
                NO MAXVALUE
                NO CYCLE
                CACHE 1
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('DROP SEQUENCE IF EXISTS enrollment_number_seq');
    }
};
