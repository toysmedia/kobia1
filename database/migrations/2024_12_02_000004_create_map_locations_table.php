<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['router', 'subscriber', 'tower', 'cabinet', 'other'])->default('other');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('description')->nullable();
            $table->string('locatable_type')->nullable();
            $table->unsignedBigInteger('locatable_id')->nullable();
            $table->string('icon')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['locatable_type', 'locatable_id']);
        });

        // Add lat/lng to routers
        if (!Schema::hasColumn('routers', 'latitude')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('is_active');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                $table->string('api_username')->nullable()->default('admin')->after('longitude');
                $table->string('api_password')->nullable()->after('api_username');
                $table->unsignedSmallInteger('api_port')->nullable()->default(8728)->after('api_password');
            });
        }

        // Add lat/lng to subscribers
        if (!Schema::hasColumn('subscribers', 'latitude')) {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('status');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('map_locations');

        Schema::table('routers', function (Blueprint $table) {
            $cols = ['latitude', 'longitude', 'api_username', 'api_password', 'api_port'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('routers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('subscribers', function (Blueprint $table) {
            foreach (['latitude', 'longitude'] as $col) {
                if (Schema::hasColumn('subscribers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
