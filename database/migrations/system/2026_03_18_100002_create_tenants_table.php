<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the tenants table in the system database.
 * Each tenant represents an ISP admin with their isolated database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('subdomain')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('database_name');
            $table->string('database_host')->default('127.0.0.1');
            $table->unsignedSmallInteger('database_port')->default(3306);
            $table->string('database_username');
            $table->text('database_password'); // encrypted at rest via Laravel Crypt (AES-256-CBC)
            $table->foreignId('plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
            $table->enum('status', ['active', 'suspended', 'trial', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('subdomain');
            $table->index('domain');
            $table->index('status');
            $table->index('plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
