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
        Schema::table('users', function (Blueprint $table) {
            // Rename full_name to name
            $table->renameColumn('full_name', 'name');
            
            // Make username nullable
            $table->string('username', 50)->nullable()->change();
            
            // Drop current_task_status column
            $table->dropColumn('current_task_status');
            
            // Drop status column
            $table->dropColumn('status');
            
            // Update role enum to include developer and designer
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'leader', 'developer', 'designer', 'user') NOT NULL DEFAULT 'user'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename name back to full_name
            $table->renameColumn('name', 'full_name');
            
            // Make username required
            $table->string('username', 50)->nullable(false)->change();
            
            // Add back current_task_status column
            $table->enum('current_task_status', ['idle', 'working'])->default('idle');
            
            // Add back status column
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Revert role enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'leader', 'user') NOT NULL DEFAULT 'user'");
        });
    }
};
