<?php

use App\Enum\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', InvoiceStatus::list())->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('invoices')
            ->whereIn('status', [
                InvoiceStatus::VOID->value,
                InvoiceStatus::UNCOLLECTIBLE->value,
            ])
            ->update(['status' => InvoiceStatus::UNPAID->value]);

        // Pre-migration column only allowed these three values (not InvoiceStatus::list()).
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', [
                InvoiceStatus::UNPAID->value,
                InvoiceStatus::PAID->value,
                InvoiceStatus::PARTIAL->value,
            ])->change();
        });
    }
};
