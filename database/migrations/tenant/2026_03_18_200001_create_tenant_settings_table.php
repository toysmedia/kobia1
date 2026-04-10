<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the tenant_settings table in each tenant database.
 * Stores key-value configuration specific to each tenant ISP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->enum('group', [
                'payment_credentials',
                'branding',
                'mikrotik',
                'notifications',
                'whitelabel',
                'general',
            ])->default('general');
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique('key');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
