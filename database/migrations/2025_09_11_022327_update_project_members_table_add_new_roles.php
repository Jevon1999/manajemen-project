<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            // Update role enum untuk project member
            $table->enum('role', ['project_manager', 'developer', 'designer'])->default('developer')->change();
        });
        
        // Update existing data
        DB::statement("UPDATE project_members SET role = 'project_manager' WHERE role = 'member'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            // Kembalikan ke role lama
            $table->enum('role', ['member', 'manager'])->default('member')->change();
        });
    }
};
