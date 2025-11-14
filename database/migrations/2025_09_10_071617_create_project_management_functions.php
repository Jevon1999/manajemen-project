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
        // Drop existing functions if they exist
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_project_completion_rate');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_total_project_hours');
        DB::unprepared('DROP TRIGGER IF EXISTS update_user_status_on_assignment');
        DB::unprepared('DROP TRIGGER IF EXISTS reset_user_status_on_completion');
        
        // Fungsi untuk menghitung tingkat penyelesaian proyek
        DB::unprepared('
            CREATE FUNCTION calculate_project_completion_rate(project_id_param INT) 
            RETURNS DECIMAL(5,2)
            DETERMINISTIC
            BEGIN
                DECLARE total_cards INT;
                DECLARE completed_cards INT;
                DECLARE completion_rate DECIMAL(5,2);
                
                SELECT COUNT(*) INTO total_cards FROM cards 
                WHERE board_id IN (SELECT board_id FROM boards WHERE project_id = project_id_param);
                
                SELECT COUNT(*) INTO completed_cards FROM cards 
                WHERE board_id IN (SELECT board_id FROM boards WHERE project_id = project_id_param) 
                AND status = "done";
                
                IF total_cards = 0 THEN
                    SET completion_rate = 0;
                ELSE
                    SET completion_rate = (completed_cards / total_cards) * 100;
                END IF;
                
                RETURN completion_rate;
            END
        ');

        // Fungsi untuk menghitung total jam yang dihabiskan dalam proyek
        DB::unprepared('
            CREATE FUNCTION calculate_total_project_hours(project_id_param INT) 
            RETURNS DECIMAL(10,2)
            DETERMINISTIC
            BEGIN
                DECLARE total_hours DECIMAL(10,2);
                
                SELECT COALESCE(SUM(duration_minutes) / 60, 0) INTO total_hours 
                FROM time_logs
                JOIN cards ON time_logs.card_id = cards.card_id
                JOIN boards ON cards.board_id = boards.board_id
                WHERE boards.project_id = project_id_param;
                
                RETURN total_hours;
            END
        ');

        // Trigger untuk memperbarui status pengguna ketika diberi tugas
        DB::unprepared('
            CREATE TRIGGER update_user_status_on_assignment
            AFTER INSERT ON card_assignments
            FOR EACH ROW
            BEGIN
                UPDATE users SET current_task_status = "working"
                WHERE user_id = NEW.user_id;
            END
        ');

        // Trigger untuk mengatur status pengguna ketika menyelesaikan tugas
        DB::unprepared('
            CREATE TRIGGER reset_user_status_on_completion
            AFTER UPDATE ON card_assignments
            FOR EACH ROW
            BEGIN
                IF NEW.assignment_status = "completed" AND OLD.assignment_status != "completed" THEN
                    IF NOT EXISTS (
                        SELECT 1 FROM card_assignments 
                        WHERE user_id = NEW.user_id 
                        AND assignment_id != NEW.assignment_id
                        AND assignment_status != "completed"
                    ) THEN
                        UPDATE users SET current_task_status = "idle"
                        WHERE user_id = NEW.user_id;
                    END IF;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_user_status_on_assignment');
        DB::unprepared('DROP TRIGGER IF EXISTS reset_user_status_on_completion');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_project_completion_rate');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_total_project_hours');
    }
};
