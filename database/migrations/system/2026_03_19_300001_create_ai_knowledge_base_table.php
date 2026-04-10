<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->create('ai_knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->text('question');
            $table->text('answer');
            $table->json('keywords')->nullable();
            $table->json('portal_context')->nullable();
            $table->string('language', 10)->default('en');
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('category');
            $table->index('language');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('ai_knowledge_base');
    }
};
