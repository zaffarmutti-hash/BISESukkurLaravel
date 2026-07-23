<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the PostgreSQL sequence (if it exists)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('DROP SEQUENCE IF EXISTS enrollment_number_seq');
        }

        // 2. Drop the redundant invoices table
        Schema::dropIfExists('invoices');

        // 3. Drop foreign keys on challan_students before renaming tables/columns
        Schema::table('challan_students', function (Blueprint $table) {
            $table->dropForeign(['challan_id']);
        });

        // 4. Rename challans to invoices
        Schema::rename('challans', 'invoices');

        // 5. Rename columns in the new invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('challan_number', 'invoice_number');
            $table->renameColumn('challan_type', 'invoice_type');
        });

        // 6. Rename challan_students to invoice_students
        Schema::rename('challan_students', 'invoice_students');

        // 7. Rename challan_id to invoice_id and recreate the foreign key constraint
        Schema::table('invoice_students', function (Blueprint $table) {
            $table->renameColumn('challan_id', 'invoice_id');
        });

        Schema::table('invoice_students', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });

        // 8. Rebuild the invoice_sequences table schema
        Schema::dropIfExists('invoice_sequences');
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('next_sequence')->default(1);
            $table->timestamps();
        });

        // Seed the initial invoice sequence row
        DB::table('invoice_sequences')->insert([
            'id' => 1,
            'next_sequence' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 9. Create the enrollment_sequences table
        Schema::dropIfExists('enrollment_sequences');
        Schema::create('enrollment_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('subject_group', 50);
            $table->unsignedInteger('next_sequence')->default(1);
            $table->timestamps();

            $table->unique(['academic_year_id', 'school_id', 'subject_group'], 'enroll_seq_comb_unique');
        });

        // 10. Update tehsils table columns
        Schema::dropIfExists('tehsils');
        Schema::create('tehsils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->timestamps();
        });

        // 11. Rename code to username in schools table and add tehsil_id
        Schema::table('schools', function (Blueprint $table) {
            $table->renameColumn('code', 'username');
            $table->foreignId('tehsil_id')->nullable()->constrained('tehsils')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Revert schools changes
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['tehsil_id']);
            $table->dropColumn('tehsil_id');
            $table->renameColumn('username', 'code');
        });

        // Recreate stubs
        Schema::dropIfExists('tehsils');
        Schema::create('tehsils', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::dropIfExists('enrollment_sequences');

        Schema::dropIfExists('invoice_sequences');
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Revert invoices/students tables
        Schema::table('invoice_students', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->renameColumn('invoice_id', 'challan_id');
        });

        Schema::rename('invoice_students', 'challan_students');

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('invoice_type', 'challan_type');
            $table->renameColumn('invoice_number', 'challan_number');
        });

        Schema::rename('invoices', 'challans');

        Schema::table('challan_students', function (Blueprint $table) {
            $table->foreign('challan_id')->references('id')->on('challans')->cascadeOnDelete();
        });

        // Recreate default invoices stub table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Recreate PG sequence
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('CREATE SEQUENCE IF NOT EXISTS enrollment_number_seq START WITH 100001 INCREMENT BY 1');
        }
    }
};
