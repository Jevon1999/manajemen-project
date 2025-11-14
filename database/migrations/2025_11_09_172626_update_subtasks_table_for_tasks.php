<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update subtasks table from card-based to task-based system
     */
    public function up(): void
    {
        // Clear existing data since we're changing the structure completely
        DB::table('subtasks')->delete();
        
        // Check which columns exist
        $columns = Schema::getColumnListing('subtasks');
        
        Schema::table('subtasks', function (Blueprint $table) use ($columns) {
            // Drop old foreign key and columns if they exist
            if (in_array('card_id', $columns)) {
                $table->dropForeign(['card_id']);
                $table->dropColumn(['card_id', 'subtaks_title', 'status', 'estimated_hours', 'actual_hours', 'position']);
            }
            
            // Add new columns only if they don't exist
            if (!in_array('task_id', $columns)) {
                $table->unsignedBigInteger('task_id')->after('subtask_id');
            }
            if (!in_array('title', $columns)) {
                $table->string('title')->after('task_id');
            }
            if (!in_array('priority', $columns)) {
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('description');
            }
            if (!in_array('is_completed', $columns)) {
                $table->boolean('is_completed')->default(false)->after('priority');
            }
            if (!in_array('created_by', $columns)) {
                $table->unsignedBigInteger('created_by')->after('is_completed');
            }
            if (!in_array('completed_at', $columns)) {
                $table->timestamp('completed_at')->nullable()->after('created_by');
            }
        });
        
        // Add foreign keys and indexes separately
        Schema::table('subtasks', function (Blueprint $table) {
            // Check if foreign keys exist before adding
            try {
                $table->foreign('task_id')->references('task_id')->on('tasks')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key may already exist
            }
            
            try {
                $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key may already exist
            }
            
            // Add index
            try {
                $table->index('is_completed');
            } catch (\Exception $e) {
                // Index may already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subtasks', function (Blueprint $table) {
            // Drop new foreign keys
            $table->dropForeign(['task_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['is_completed']);
            
            // Drop new columns
            $table->dropColumn(['task_id', 'title', 'priority', 'is_completed', 'created_by', 'completed_at']);
            
            // Restore old columns
            $table->unsignedBigInteger('card_id')->after('subtask_id');
            $table->string('subtaks_title', 100)->after('card_id');
            $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo')->after('description');
            $table->decimal('estimated_hours', 5, 2)->nullable()->after('status');
            $table->decimal('actual_hours', 5, 2)->nullable()->after('estimated_hours');
            $table->integer('position')->default(0)->after('actual_hours');
            
            // Restore old foreign key
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
        });
    }
};
