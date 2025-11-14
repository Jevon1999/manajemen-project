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
        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('report_type', 50); // 'combined', 'project', 'task', 'work_session', 'user_performance'
            $table->json('filters')->nullable(); // Store filter parameters
            $table->string('file_path')->nullable(); // Path to generated CSV file
            $table->timestamp('generated_at');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('report_type');
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};
