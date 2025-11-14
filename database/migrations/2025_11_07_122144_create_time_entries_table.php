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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id('time_entry_id');
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('user_id');
            $table->date('work_date');
            $table->decimal('hours_spent', 5, 2); // Max 999.99 hours
            $table->text('description')->nullable();
            $table->enum('entry_type', ['manual', 'timer'])->default('manual');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['card_id', 'work_date']);
            $table->index(['user_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
