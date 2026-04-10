<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mysql';

    public function up(): void {
        Schema::connection('mysql')->create('community_post_tag', function (Blueprint $table) {
            $table->foreignId('community_post_id')->constrained('community_posts')->cascadeOnDelete();
            $table->foreignId('community_tag_id')->constrained('community_tags')->cascadeOnDelete();
            $table->primary(['community_post_id', 'community_tag_id']);
        });
    }

    public function down(): void {
        Schema::connection('mysql')->dropIfExists('community_post_tag');
    }
};
