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
        // Cara aman untuk mengubah enum
        
        // 1. Tambah kolom baru
        Schema::table('project_members', function (Blueprint $table) {
            $table->enum('new_role', ['project_manager', 'developer', 'designer'])->default('developer')->after('role');
        });
        
        // 2. Copy data ke kolom baru dengan mapping
        DB::statement("
            UPDATE project_members 
            SET new_role = CASE 
                WHEN role = 'manager' THEN 'project_manager'
                WHEN role = 'member' THEN 'developer'
                ELSE 'developer'
            END
        ");
        
        // 3. Drop kolom lama
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        // 4. Rename kolom baru
        Schema::table('project_members', function (Blueprint $table) {
            $table->renameColumn('new_role', 'role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->enum('old_role', ['member', 'manager'])->default('member')->after('role');
        });
        
        DB::statement("
            UPDATE project_members 
            SET old_role = CASE 
                WHEN role = 'project_manager' THEN 'manager'
                WHEN role IN ('developer', 'designer') THEN 'member'
                ELSE 'member'
            END
        ");
        
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('project_members', function (Blueprint $table) {
            $table->renameColumn('old_role', 'role');
        });
    }
};
