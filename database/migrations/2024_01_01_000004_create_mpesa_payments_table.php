<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mpesa_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->nullable()->constrained('subscribers')->nullOnDelete();
            $table->string('phone', 15);
            $table->decimal('amount', 10, 2);
            $table->string('mpesa_receipt_number')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->string('mpesa_reference')->nullable();
            $table->string('account_reference')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->foreignId('isp_package_id')->nullable()->constrained('isp_packages')->nullOnDelete();
            $table->enum('connection_type', ['pppoe', 'hotspot'])->default('hotspot');
            $table->string('checkout_request_id')->nullable();
            $table->string('merchant_request_id')->nullable();
            $table->json('raw_callback')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mpesa_payments'); }
};
