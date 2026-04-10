<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mysql';

    public function up(): void {
        Schema::connection('mysql')->create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_user_id')->constrained('community_users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('community_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body');
            $table->enum('type', ['question', 'discussion', 'article', 'announcement'])->default('discussion');
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])->default('approved');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::connection('mysql')->dropIfExists('community_posts');
    }
};
