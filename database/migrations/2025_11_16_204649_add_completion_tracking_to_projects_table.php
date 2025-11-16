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
        Schema::table('projects', function (Blueprint $table) {
            // Completion tracking fields (completed_at already exists)
            if (!Schema::hasColumn('projects', 'delay_days')) {
                $table->integer('delay_days')->default(0)->after('completed_at');
            }
            if (!Schema::hasColumn('projects', 'delay_reason')) {
                $table->text('delay_reason')->nullable()->after('delay_days');
            }
            if (!Schema::hasColumn('projects', 'completion_notes')) {
                $table->text('completion_notes')->nullable()->after('delay_reason');
            }
            if (!Schema::hasColumn('projects', 'is_overdue')) {
                $table->boolean('is_overdue')->default(false)->after('completion_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'completed_at',
                'delay_days',
                'delay_reason',
                'completion_notes',
                'is_overdue'
            ]);
        });
    }
};
