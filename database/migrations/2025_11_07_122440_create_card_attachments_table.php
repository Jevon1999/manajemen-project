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
        Schema::create('card_attachments', function (Blueprint $table) {
            $table->id('attachment_id');
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // image/png, application/pdf, etc
            $table->integer('file_size'); // in bytes
            $table->string('original_name');
            $table->enum('attachment_type', ['design', 'document', 'code', 'image', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('user_id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['card_id', 'created_at']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_attachments');
    }
};
