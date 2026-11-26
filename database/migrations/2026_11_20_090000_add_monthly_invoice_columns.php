<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->unique()->after('stripe_subscription_status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('billing_period', 7)->nullable()->after('due_date');
            $table->timestamp('reminded_at')->nullable()->after('stripe_hosted_invoice_url');
            $table->unique(['student_id', 'billing_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'billing_period']);
            $table->dropColumn(['billing_period', 'reminded_at']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['stripe_customer_id']);
            $table->dropColumn('stripe_customer_id');
        });
    }
};
