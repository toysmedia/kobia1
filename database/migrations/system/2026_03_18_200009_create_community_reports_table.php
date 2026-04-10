<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'mysql';

    public function up(): void {
        Schema::connection('mysql')->create('community_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_user_id')->constrained('community_users')->cascadeOnDelete();
            $table->morphs('reportable');
            $table->enum('reason', ['spam', 'harassment', 'inappropriate', 'misinformation', 'other']);
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed', 'actioned'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('super_admin_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('action_taken')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::connection('mysql')->dropIfExists('community_reports');
    }
};
