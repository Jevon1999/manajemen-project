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
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->string('type')->default('text'); // text, mention, system
            $table->unsignedBigInteger('parent_id')->nullable(); // for replies
            $table->timestamps();
            
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('comment_id')->on('task_comments')->onDelete('cascade');
            
            $table->index(['card_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};
