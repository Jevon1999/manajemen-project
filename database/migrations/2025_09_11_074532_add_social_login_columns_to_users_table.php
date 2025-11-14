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
        Schema::table('users', function (Blueprint $table) {
            // Add name column for compatibility
            $table->string('name')->nullable()->after('full_name');
            
            // Add avatar column
            $table->string('avatar')->nullable()->after('role');
            
            // Add social provider columns - hanya Google dan GitHub
            $table->string('google_id')->nullable()->after('avatar');
            $table->text('google_token')->nullable()->after('google_id');
            
            $table->string('github_id')->nullable()->after('google_token');
            $table->text('github_token')->nullable()->after('github_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'avatar',
                'google_id',
                'google_token',
                'github_id', 
                'github_token'
            ]);
        });
    }
};
