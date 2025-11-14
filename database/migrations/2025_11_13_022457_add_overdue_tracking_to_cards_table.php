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
        Schema::table('cards', function (Blueprint $table) {
            $table->timestamp('last_overdue_alert_at')->nullable()
                ->comment('Last time overdue alert was sent');
            $table->timestamp('last_escalation_at')->nullable()
                ->comment('Last time critical escalation was sent');
            $table->integer('overdue_notification_count')->default(0)
                ->comment('Number of overdue notifications sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['last_overdue_alert_at', 'last_escalation_at', 'overdue_notification_count']);
        });
    }
};
