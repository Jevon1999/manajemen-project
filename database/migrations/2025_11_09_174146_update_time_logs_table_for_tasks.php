<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update time_logs table from card-based to task-based system
     */
    public function up(): void
    {
        // Clear existing data
        DB::table('time_logs')->delete();
        
        // Check which columns exist
        $columns = Schema::getColumnListing('time_logs');
        
        Schema::table('time_logs', function (Blueprint $table) use ($columns) {
            // Drop old foreign keys and columns if they exist
            if (in_array('card_id', $columns)) {
                $table->dropForeign(['card_id']);
                $table->dropColumn('card_id');
            }
            
            if (in_array('subtask_id', $columns)) {
                $table->dropForeign(['subtask_id']);
                $table->dropColumn('subtask_id');
            }
            
            // Rename/change columns
            if (in_array('log_id', $columns)) {
                $table->renameColumn('log_id', 'timelog_id');
            }
            
            if (in_array('description', $columns)) {
                $table->renameColumn('description', 'notes');
            }
            
            if (in_array('duration_minutes', $columns)) {
                $table->dropColumn('duration_minutes');
            }
        });
        
        // Add new columns
        Schema::table('time_logs', function (Blueprint $table) use ($columns) {
            if (!in_array('task_id', $columns)) {
                $table->unsignedBigInteger('task_id')->after('timelog_id');
            }
            
            if (!in_array('duration_seconds', $columns)) {
                $table->integer('duration_seconds')->nullable()
                      ->comment('Duration in seconds, calculated when timer stops')
                      ->after('end_time');
            }
        });
        
        // Add foreign keys and indexes
        Schema::table('time_logs', function (Blueprint $table) {
            try {
                $table->foreign('task_id')->references('task_id')->on('tasks')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key may already exist
            }
            
            try {
                $table->index('task_id');
                $table->index('start_time');
                $table->index(['task_id', 'user_id']);
            } catch (\Exception $e) {
                // Indexes may already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            // Drop new structures
            $table->dropForeign(['task_id']);
            $table->dropIndex(['task_id']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['task_id', 'user_id']);
            
            $table->dropColumn(['task_id', 'duration_seconds']);
            
            // Restore old structure
            $table->renameColumn('timelog_id', 'log_id');
            $table->renameColumn('notes', 'description');
            
            $table->unsignedBigInteger('card_id')->after('log_id');
            $table->unsignedBigInteger('subtask_id')->nullable()->after('card_id');
            $table->integer('duration_minutes')->nullable()->after('end_time');
            
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('subtask_id')->references('subtask_id')->on('subtasks')->onDelete('cascade');
        });
    }
};
