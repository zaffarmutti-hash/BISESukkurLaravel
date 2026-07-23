<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Additional surcharge amount applied during grace period
            // 0 means no extra charge during grace (though this is unusual)
            $table->unsignedInteger('late_fee_surcharge_paisas')->default(0)->after('amount_paisas');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn('late_fee_surcharge_paisas');
        });
    }
};
