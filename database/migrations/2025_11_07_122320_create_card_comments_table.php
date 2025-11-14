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
        Schema::create('card_comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->boolean('is_progress_update')->default(false);
            $table->integer('progress_percentage')->nullable(); // 0-100
            $table->enum('comment_type', ['general', 'progress', 'blocker', 'question', 'feedback'])->default('general');
            $table->unsignedBigInteger('parent_id')->nullable(); // For threaded comments
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('comment_id')->on('card_comments')->onDelete('cascade');

            // Indexes
            $table->index(['card_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_comments');
    }
};
