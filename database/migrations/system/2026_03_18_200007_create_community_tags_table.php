<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mysql';

    public function up(): void {
        Schema::connection('mysql')->create('community_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::connection('mysql')->dropIfExists('community_tags');
    }
};
