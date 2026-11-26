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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->unique()->after('remember_token');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->unique()->after('phone');
            $table->string('stripe_subscription_status')->nullable()->after('stripe_subscription_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('stripe_invoice_id')->nullable()->unique()->after('status');
            $table->text('stripe_hosted_invoice_url')->nullable()->after('stripe_invoice_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_invoice_id')->nullable()->unique()->after('stripe_checkout_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['stripe_invoice_id']);
            $table->dropColumn('stripe_invoice_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['stripe_invoice_id']);
            $table->dropColumn(['stripe_invoice_id', 'stripe_hosted_invoice_url']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['stripe_subscription_id']);
            $table->dropColumn(['stripe_subscription_id', 'stripe_subscription_status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['stripe_customer_id']);
            $table->dropColumn('stripe_customer_id');
        });
    }
};
