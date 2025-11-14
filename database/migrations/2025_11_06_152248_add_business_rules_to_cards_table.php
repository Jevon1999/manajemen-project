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
        Schema::table('cards', function (Blueprint $table) {
            // Rule 1: One active task per developer
            $table->boolean('is_active')->default(false)->after('status')
                ->comment('Indicates if this task is currently active for the assigned developer');
            
            // Rule 4: Approval required before completion
            $table->boolean('requires_approval')->default(true)->after('is_active')
                ->comment('Indicates if task completion requires approval');
            $table->unsignedBigInteger('approved_by')->nullable()->after('requires_approval')
                ->comment('User ID who approved the task');
            $table->timestamp('approved_at')->nullable()->after('approved_by')
                ->comment('Timestamp when task was approved');
            
            // Rule 2 & 3: Time tracking and daily comments
            $table->timestamp('last_progress_update')->nullable()->after('approved_at')
                ->comment('Last time progress was updated (for daily comment check)');
            $table->boolean('has_time_log_today')->default(false)->after('last_progress_update')
                ->comment('Flag to check if developer logged time today');
            
            // Rule 5: Priority-based assignment
            $table->integer('assignment_score')->default(0)->after('priority')
                ->comment('Score for priority-based assignment algorithm');
            $table->timestamp('started_at')->nullable()->after('assignment_score')
                ->comment('When developer started working on this task');
            $table->timestamp('completed_at')->nullable()->after('started_at')
                ->comment('When task was actually completed (before approval)');
            
            // Add foreign key for approval
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'is_active',
                'requires_approval',
                'approved_by',
                'approved_at',
                'last_progress_update',
                'has_time_log_today',
                'assignment_score',
                'started_at',
                'completed_at',
            ]);
        });
    }
};
