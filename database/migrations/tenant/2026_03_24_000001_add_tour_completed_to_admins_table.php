<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tour_completed flag to the tenant admins table.
 * Used by the onboarding guided tour to track first-login state.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('tenant')->hasTable('admins')) {
            Schema::connection('tenant')->table('admins', function (Blueprint $table) {
                if (! Schema::connection('tenant')->hasColumn('admins', 'tour_completed')) {
                    $table->boolean('tour_completed')->default(false)->after('password');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('tenant')->hasTable('admins') &&
            Schema::connection('tenant')->hasColumn('admins', 'tour_completed')) {
            Schema::connection('tenant')->table('admins', function (Blueprint $table) {
                $table->dropColumn('tour_completed');
            });
        }
    }
};