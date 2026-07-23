<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Permanently records which phase this invoice was generated under
            $table->enum('fee_phase', ['normal', 'grace'])->default('normal')->after('status');
            // Total late fee surcharge on this invoice (sum of all student surcharges)
            $table->unsignedInteger('late_fee_surcharge_paisas')->default(0)->after('total_amount_paisas');
        });

        Schema::table('invoice_students', function (Blueprint $table) {
            // Per-student surcharge component for audit trail
            $table->unsignedInteger('late_fee_surcharge_paisas')->default(0)->after('amount_paisas');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['fee_phase', 'late_fee_surcharge_paisas']);
        });

        Schema::table('invoice_students', function (Blueprint $table) {
            $table->dropColumn('late_fee_surcharge_paisas');
        });
    }
};
