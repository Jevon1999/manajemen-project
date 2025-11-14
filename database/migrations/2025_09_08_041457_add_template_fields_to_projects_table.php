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
            // Add template_id column for project templates
            $table->unsignedBigInteger('template_id')->nullable()->after('status');
            
            // Add additional project management fields
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->after('template_id');
            $table->enum('category', [
                'web_development',
                'mobile_app', 
                'desktop_software',
                'data_analysis',
                'marketing',
                'design',
                'research',
                'other'
            ])->default('other')->after('priority');
            
            $table->decimal('budget', 12, 2)->nullable()->after('category');
            $table->boolean('notifications_enabled')->default(true)->after('budget');
            $table->boolean('public_visibility')->default(false)->after('notifications_enabled');
            $table->boolean('allow_member_invite')->default(true)->after('public_visibility');
            
            // Project tracking fields
            $table->integer('completion_percentage')->default(0)->after('allow_member_invite');
            $table->timestamp('last_activity_at')->nullable()->after('completion_percentage');
            
            // Archive and soft delete support
            $table->boolean('is_archived')->default(false)->after('last_activity_at');
            $table->softDeletes()->after('is_archived');

            // Indexes for better performance
            $table->index(['status', 'category']);
            $table->index(['priority', 'created_at']);
            $table->index('template_id');
            $table->index('is_archived');
            $table->index('public_visibility');

            // Foreign key constraint for template
            $table->foreign('template_id')->references('id')->on('project_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key and indexes first
            $table->dropForeign(['template_id']);
            $table->dropIndex(['status', 'category']);
            $table->dropIndex(['priority', 'created_at']);
            $table->dropIndex(['template_id']);
            $table->dropIndex(['is_archived']);
            $table->dropIndex(['public_visibility']);
            
            // Drop columns
            $table->dropColumn([
                'template_id',
                'priority', 
                'category',
                'budget',
                'notifications_enabled',
                'public_visibility', 
                'allow_member_invite',
                'completion_percentage',
                'last_activity_at',
                'is_archived',
                'deleted_at'
            ]);
        });
    }
};