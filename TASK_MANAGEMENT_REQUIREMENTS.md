# Task Management System - Complete Requirements

## ATURAN UTAMA
**1 TASK = 1 USER** - User tidak boleh punya lebih dari 1 active task

## 1. PROJECT MANAGEMENT

### Create Project
- [x] Status field DIHAPUS dari form create
- [x] Status otomatis 'planning' saat create
- [ ] Tombol "Mark as Completed" untuk leader

### Project Completion
- Leader bisa klik "Project Selesai" 
- Project pindah ke status 'completed'

## 2. TASK MANAGEMENT

### Task Assignment Rules
- Leader create task
- Leader assign task ke 1 user
- User HARUS TIDAK punya task aktif lain
- Jika user sudah punya task aktif → GAGAL

### Task Lifecycle
1. **TODO** - Task baru dibuat oleh leader → masuk board TODO
2. **IN PROGRESS** - User klik START timer pertama kali → otomatis pindah ke IN_PROGRESS
   - User bisa STOP timer (masih tetap di IN_PROGRESS)
   - User bisa START lagi (masih tetap di IN_PROGRESS)
3. **REVIEW** - User klik "Selesaikan Task" → otomatis pindah ke REVIEW
4. **DONE** - Leader review dan approve → pindah ke DONE

## 3. SUBTASK SYSTEM

### Features
- User create subtask di task yang mereka kerjakan
- Subtask punya priority: LOW, MEDIUM, HIGH
- Subtask berfungsi sebagai TODO LIST
- Leader bisa lihat semua subtasks
- User bisa mark "Subtask Telah Diselesaikan"

### Database Structure
```
subtasks:
- subtask_id (PK)
- task_id (FK)
- title
- description
- priority (enum: low, medium, high)
- is_completed (boolean)
- created_by (FK to users)
- completed_at (datetime)
- timestamps
```

## 4. TIME LOG SYSTEM

### Features
- Tombol START/STOP di dalam task detail
- Data tersimpan di database
- Kalkulasi otomatis saat START lagi (akumulasi waktu)
- User bisa pause/resume kapan saja

### Database Structure
```
time_logs:
- time_log_id (PK)
- task_id (FK)
- user_id (FK)
- start_time (datetime)
- end_time (datetime, nullable)
- duration_seconds (integer, calculated)
- notes (text, nullable)
- timestamps
```

### Logic
- START: create new time_log dengan start_time = now()
- STOP: update time_log.end_time = now(), calculate duration
- START lagi: create new time_log entry
- Total duration: SUM semua time_logs untuk task

## 5. BOARD SYSTEM

### Board Types
1. **TODO** - Task baru masuk sini
2. **IN PROGRESS** - Task yang sedang dikerjakan (ada active time log)
3. **REVIEW** - Task selesai, menunggu review leader
4. **DONE** - Task approved oleh leader

### Auto Transitions
- Create task → TODO
- First START → IN_PROGRESS  
- STOP → tetap IN_PROGRESS
- START lagi → tetap IN_PROGRESS
- User click "Selesaikan" → REVIEW
- Leader click "Approve" → DONE

## 6. KOMENTAR SYSTEM

### Features
- Komentar dibuat di dalam task
- User dan leader bisa comment
- Real-time updates (optional)

### Database Structure
```
task_comments:
- comment_id (PK)
- task_id (FK)
- user_id (FK)
- comment (text)
- created_at
- updated_at
```

## 7. NOTIFIKASI SYSTEM

### Notification Types
- Admin menambahkan user sebagai leader di project X
- Leader assign task ke user
- User menyelesaikan task (notify leader)
- Leader approve/reject task (notify user)
- Project deadline approaching

### Database Structure
```
notifications:
- notification_id (PK)
- user_id (FK) 
- type (string: task_assigned, task_completed, etc)
- title (string)
- message (text)
- data (json)
- is_read (boolean)
- created_at
```

## 8. LEADERBOARD

### Metrics
1. **Most Tasks Completed** - User dengan task complete terbanyak
2. **Fastest Completion** - User dengan waktu pengerjaan paling sedikit tapi task selesai

### Query Logic
```sql
-- Most completed
SELECT user_id, COUNT(*) as completed_count
FROM tasks
WHERE status = 'done' AND assigned_to IS NOT NULL
GROUP BY user_id
ORDER BY completed_count DESC
LIMIT 10

-- Fastest average time
SELECT user_id, AVG(total_duration) as avg_duration
FROM (
  SELECT t.user_id, t.task_id, SUM(tl.duration_seconds) as total_duration
  FROM tasks t
  JOIN time_logs tl ON t.task_id = tl.task_id
  WHERE t.status = 'done'
  GROUP BY t.user_id, t.task_id
) as task_durations
GROUP BY user_id
ORDER BY avg_duration ASC
LIMIT 10
```

## 9. REPORT SYSTEM

### Report Types

#### A. Per Project Report
- Project info (name, leader, dates)
- Total tasks (todo, in_progress, review, done)
- Total time spent (sum of all time logs)
- Member list dengan task count masing-masing
- Average completion time per task
- CSV Export

#### B. Global Report  
- All projects statistics
- Total tasks across all projects
- Total users active
- Average project completion time
- Top performers (leaderboard data)
- CSV Export

### CSV Format
```
Project Report CSV:
Project Name, Task Title, Assigned To, Status, Time Spent (hours), Completed At

Global Report CSV:
Project Name, Total Tasks, Completed Tasks, Total Time, Leader, Status, Created At
```

## IMPLEMENTATION PRIORITY

### Phase 1 (Core Features)
1. ✅ Remove status dari create project
2. [ ] Add "Mark as Completed" button
3. [ ] 1 Task = 1 User validation
4. [ ] Subtask CRUD
5. [ ] TimeLog start/stop

### Phase 2 (Board System)
6. [ ] Board transitions logic
7. [ ] Task status updates
8. [ ] UI untuk boards view

### Phase 3 (Engagement Features)
9. [ ] Comments system
10. [ ] Notifications
11. [ ] Leaderboard

### Phase 4 (Analytics)
12. [ ] Report per project
13. [ ] Global report
14. [ ] CSV export

## NOTES
- Semua perubahan status harus ter-log untuk audit
- Time tracking harus akurat (timezone aware)
- Notifications harus real-time atau near real-time
- Leaderboard cache untuk performance
- CSV export gunakan queue untuk file besar
