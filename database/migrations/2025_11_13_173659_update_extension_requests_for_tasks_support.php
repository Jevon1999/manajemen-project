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
        Schema::table('extension_requests', function (Blueprint $table) {
            // Make card_id nullable since we'll support both cards and tasks
            $table->unsignedBigInteger('card_id')->nullable()->change();
            
            // Add task_id column
            $table->unsignedBigInteger('task_id')->nullable()->after('card_id');
            $table->foreign('task_id')->references('task_id')->on('tasks')->onDelete('cascade');
            
            // Add type column to distinguish between card and task
            $table->enum('entity_type', ['card', 'task'])->default('card')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extension_requests', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn(['task_id', 'entity_type']);
            $table->unsignedBigInteger('card_id')->nullable(false)->change();
        });
    }
};
