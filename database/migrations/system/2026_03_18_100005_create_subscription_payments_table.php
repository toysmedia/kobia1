<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the subscription_payments table in the system database.
 * Tracks tenant subscription payments collected by Super Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('mpesa');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('mpesa_receipt_number')->nullable()->unique();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
