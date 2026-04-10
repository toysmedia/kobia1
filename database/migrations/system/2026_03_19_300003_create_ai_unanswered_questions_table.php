<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->create('ai_unanswered_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->text('question');
            $table->string('portal');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('status')->default('pending');
            $table->text('resolved_answer')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('portal');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('ai_unanswered_questions');
    }
};
