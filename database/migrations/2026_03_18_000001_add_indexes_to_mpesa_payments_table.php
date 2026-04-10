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
        Schema::table('mpesa_payments', function (Blueprint $table) {
            $table->unique('mpesa_receipt_number');
            $table->index('checkout_request_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mpesa_payments', function (Blueprint $table) {
            $table->dropUnique(['mpesa_receipt_number']);
            $table->dropIndex(['checkout_request_id']);
            $table->dropIndex(['status']);
        });
    }
};
