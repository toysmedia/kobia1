<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the tenant_recycle_bin table in each tenant database.
 * Stores soft-deleted records allowing tenant admin to restore data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_recycle_bin', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('data');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['model_type', 'model_id']);
            $table->index('deleted_by');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_recycle_bin');
    }
};
