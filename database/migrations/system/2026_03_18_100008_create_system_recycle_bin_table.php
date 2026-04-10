<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the system_recycle_bin table in the system database.
 * Mirrors soft-deleted records from all tenants for Super Admin visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_recycle_bin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('data');
            $table->string('deleted_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by_id')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['model_type', 'model_id']);
            $table->index('tenant_id');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_recycle_bin');
    }
};
