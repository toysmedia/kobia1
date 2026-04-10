<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('portal');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->boolean('was_answered')->default(true);
            $table->boolean('was_helpful')->nullable();
            $table->boolean('flagged_for_review')->default(false);
            $table->integer('openai_tokens_used')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('portal');
            $table->index('flagged_for_review');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('ai_conversations');
    }
};
