<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracks real-time sync snapshots from each router (1-second cadence).
        Schema::create('router_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id')->index();
            $table->json('ppp_sessions')->nullable();
            $table->json('hs_sessions')->nullable();
            $table->string('cpu_load', 10)->nullable();
            $table->timestamp('synced_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });

        // Queue of commands to push to a router on its next sync/callback.
        Schema::create('pending_router_commands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id')->index();
            $table->string('command', 80);
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('result')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_router_commands');
        Schema::dropIfExists('router_sync_logs');
    }
};