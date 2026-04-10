<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mysql';

    public function up(): void {
        Schema::connection('mysql')->create('community_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->foreignId('community_user_id')->constrained('community_users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('community_replies')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_accepted')->default(false);
            $table->integer('likes_count')->default(0);
            $table->enum('status', ['visible', 'hidden', 'flagged'])->default('visible');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::connection('mysql')->dropIfExists('community_replies');
    }
};
