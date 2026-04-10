<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mysql';

    public function up(): void {
        Schema::connection('mysql')->create('community_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_user_id')->constrained('community_users')->cascadeOnDelete();
            $table->morphs('likeable');
            $table->timestamps();
            $table->unique(['community_user_id', 'likeable_id', 'likeable_type']);
        });
    }

    public function down(): void {
        Schema::connection('mysql')->dropIfExists('community_likes');
    }
};
