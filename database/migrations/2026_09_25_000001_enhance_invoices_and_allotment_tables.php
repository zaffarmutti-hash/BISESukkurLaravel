<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add missing fields to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'class_level')) {
                $table->string('class_level', 50)->nullable()->after('invoice_type');
            }
            if (!Schema::hasColumn('invoices', 'subject_group')) {
                $table->string('subject_group', 50)->nullable()->after('class_level');
            }
            if (!Schema::hasColumn('invoices', 'student_type')) {
                $table->string('student_type', 50)->nullable()->after('subject_group');
            }
            if (!Schema::hasColumn('invoices', 'challan_pdf_path')) {
                $table->string('challan_pdf_path', 500)->nullable()->after('deposit_slip_path');
            }
            if (!Schema::hasColumn('invoices', 'student_list_pdf_path')) {
                $table->string('student_list_pdf_path', 500)->nullable()->after('challan_pdf_path');
            }
        });

        // 2. Add is_included to invoice_students
        Schema::table('invoice_students', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_students', 'is_included')) {
                $table->boolean('is_included')->default(true)->after('late_fee_surcharge_paisas');
            }
        });

        // 3. Enhance invoice_sequences to support sequence_type and last_number
        Schema::table('invoice_sequences', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_sequences', 'sequence_type')) {
                $table->string('sequence_type', 50)->default('Invoice')->after('id');
            }
            if (!Schema::hasColumn('invoice_sequences', 'prefix')) {
                $table->string('prefix', 100)->nullable()->after('sequence_type');
            }
            if (!Schema::hasColumn('invoice_sequences', 'last_number')) {
                $table->unsignedInteger('last_number')->default(0)->after('next_sequence');
            }
        });

        // Sync initial invoice sequence if present
        $firstSeq = DB::table('invoice_sequences')->where('id', 1)->first();
        if ($firstSeq) {
            DB::table('invoice_sequences')->where('id', 1)->update([
                'sequence_type' => 'Invoice',
                'last_number'   => max(0, ($firstSeq->next_sequence ?? 1) - 1),
            ]);
        } else {
            DB::table('invoice_sequences')->insert([
                'id'            => 1,
                'sequence_type' => 'Invoice',
                'next_sequence' => 1,
                'last_number'   => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // 4. Add zone and allowed_levels to schools
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'zone')) {
                $table->unsignedSmallInteger('zone')->default(1)->after('gender');
            }
            if (!Schema::hasColumn('schools', 'allowed_levels')) {
                $table->json('allowed_levels')->nullable()->after('zone');
            }
        });

        // 5. Add manual allotment columns to students if not present
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'allotment_type')) {
                $table->string('allotment_type', 20)->default('auto')->after('enrollment_number_issued_at'); // auto or manual
            }
            if (!Schema::hasColumn('students', 'allotment_reason')) {
                $table->text('allotment_reason')->nullable()->after('allotment_type');
            }
            if (!Schema::hasColumn('students', 'allotted_by')) {
                $table->foreignId('allotted_by')->nullable()->constrained('users')->nullOnDelete()->after('allotment_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['class_level', 'subject_group', 'student_type', 'challan_pdf_path', 'student_list_pdf_path']);
        });

        Schema::table('invoice_students', function (Blueprint $table) {
            $table->dropColumn('is_included');
        });

        Schema::table('invoice_sequences', function (Blueprint $table) {
            $table->dropColumn(['sequence_type', 'prefix', 'last_number']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['zone', 'allowed_levels']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['allotted_by']);
            $table->dropColumn(['allotment_type', 'allotment_reason', 'allotted_by']);
        });
    }
};
