<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->after('school_id')->constrained('academic_years')->nullOnDelete();
            $table->string('invoice_number', 30)->nullable()->unique()->after('academic_year_id');
            $table->enum('invoice_type', ['enrollment', 'examination'])->default('enrollment')->after('invoice_number');
            $table->decimal('amount', 12, 2)->default(0)->after('invoice_type');
            $table->enum('status', ['draft', 'paid', 'verified', 'cancelled'])->default('draft')->after('amount');
            $table->foreignId('verified_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->timestamp('paid_at')->nullable()->after('verified_at');

            $table->index(['status', 'academic_year_id']);
            $table->index(['school_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'school_id', 'academic_year_id', 'invoice_number', 'invoice_type',
                'amount', 'status', 'verified_by', 'verified_at', 'paid_at',
            ]);
        });
    }
};
