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
        // First, ensure all projects have a leader
        DB::statement('UPDATE projects SET leader_id = (SELECT user_id FROM users WHERE role = "leader" ORDER BY user_id LIMIT 1) WHERE leader_id IS NULL');
        
        Schema::table('projects', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign('projects_leader_id_foreign');
        });
        
        Schema::table('projects', function (Blueprint $table) {
            // Make leader_id NOT NULL
            $table->unsignedBigInteger('leader_id')->nullable(false)->change();
            
            // Re-add foreign key with RESTRICT instead of SET NULL
            $table->foreign('leader_id', 'projects_leader_id_foreign')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign('projects_leader_id_foreign');
        });
        
        Schema::table('projects', function (Blueprint $table) {
            // Make nullable again
            $table->unsignedBigInteger('leader_id')->nullable()->change();
            
            // Re-add original foreign key with SET NULL
            $table->foreign('leader_id', 'projects_leader_id_foreign')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }
};
