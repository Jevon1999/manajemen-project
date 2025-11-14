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
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->enum('category', [
                'web_development',
                'mobile_app', 
                'desktop_software',
                'data_analysis',
                'marketing',
                'design',
                'research',
                'other'
            ])->default('other');
            $table->json('template_data')->nullable(); // Project structure, phases, tasks
            $table->json('default_boards')->nullable(); // Default board names
            $table->integer('estimated_duration_days')->nullable();
            $table->json('required_roles')->nullable(); // Required team roles
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['category', 'is_active']);
            $table->index('usage_count');
            $table->index('created_by');

            // Foreign key constraint
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};