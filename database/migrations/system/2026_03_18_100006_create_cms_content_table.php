<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the cms_content table in the system database.
 * Stores all guest/public page content managed by Super Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_content', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->enum('type', ['text', 'html', 'image', 'json'])->default('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['section', 'key']);
            $table->index('section');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_content');
    }
};
