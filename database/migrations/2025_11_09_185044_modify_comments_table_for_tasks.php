<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Add task_id column
            $table->unsignedBigInteger('task_id')->nullable()->after('comment_id');
            
            // Rename comment_text to comment
            $table->renameColumn('comment_text', 'comment');
            
            // Make card_id nullable (for backward compatibility)
            $table->unsignedBigInteger('card_id')->nullable()->change();
            
            // Add foreign key for task_id
            $table->foreign('task_id')->references('task_id')->on('tasks')->onDelete('cascade');
            
            // Add index for task comments
            $table->index(['task_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropIndex(['task_id', 'created_at']);
            $table->dropColumn('task_id');
            $table->renameColumn('comment', 'comment_text');
        });
    }
};
