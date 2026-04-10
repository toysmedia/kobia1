<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection('tenant')->create('ai_tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('customer_portal_ai_enabled')->default(true);
            $table->text('custom_greeting')->nullable();
            $table->json('custom_knowledge')->nullable();
            $table->integer('openai_usage_limit')->nullable();
            $table->integer('tokens_used_this_month')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('ai_tenant_settings');
    }
};
