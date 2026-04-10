<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the feature_flags table in the system database.
 * Granular feature control per pricing plan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('feature_name');
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_key']);
            $table->index('plan_id');
            $table->index('feature_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
