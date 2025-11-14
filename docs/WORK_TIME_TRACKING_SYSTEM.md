# Work Time Tracking System Documentation

## Overview
Sistem time tracking untuk mencatat waktu kerja user dengan batasan 8 jam per hari. Timer berjalan di frontend dan data durasi disimpan ke database saat user stop work.

## Fitur Utama

### 1. **Start/Stop Work Timer**
- User klik "Start Work" untuk memulai sesi kerja
- Timer berjalan di browser (frontend) dan menampilkan durasi real-time
- User klik "Stop Work" untuk menghentikan dan menyimpan durasi ke database
- Timer tetap berjalan meskipun refresh browser (menggunakan localStorage)

### 2. **Daily Work Limit (8 Hours)**
- Sistem membatasi waktu kerja maksimal 8 jam (28800 detik) per hari
- Saat mencapai 8 jam, timer otomatis stop
- User tidak bisa start work baru jika sudah mencapai limit harian

### 3. **Session Persistence**
- Active session disimpan di localStorage browser
- Jika browser refresh atau ditutup, timer tetap melanjutkan dari waktu terakhir
- Server juga menyimpan active session untuk validasi

### 4. **Real-time Display**
- Current session timer (HH:MM:SS)
- Today's total work time
- Remaining time until 8-hour limit
- Weekly summary di dashboard

## Struktur Database

### Table: `work_sessions`
```sql
- session_id (PK)
- user_id (FK to users)
- task_id (FK to tasks, nullable)
- started_at (timestamp)
- stopped_at (timestamp, nullable)
- duration_seconds (integer) - durasi dari frontend timer
- work_date (date) - untuk tracking daily limit
- status (enum: active, paused, completed)
- notes (text, nullable)
- created_at, updated_at
```

## API Endpoints

### POST `/work-sessions/start`
Start new work session
```json
Request:
{
    "task_id": 123 (optional)
}

Response:
{
    "success": true,
    "message": "Work session started",
    "session": {...},
    "today_total": 0
}
```

### POST `/work-sessions/stop`
Stop active work session
```json
Request:
{
    "session_id": 1,
    "duration_seconds": 3600,
    "notes": "optional notes"
}

Response:
{
    "success": true,
    "message": "Work session stopped",
    "session": {...},
    "today_total": 3600,
    "formatted_duration": "01:00:00"
}
```

### GET `/work-sessions/active`
Get active session for current user
```json
Response:
{
    "success": true,
    "session": {
        "session_id": 1,
        "started_at": "2025-11-11 10:00:00",
        ...
    }
}
```

### GET `/work-sessions/today-total`
Get today's total work time
```json
Response:
{
    "success": true,
    "today_total": 14400,
    "formatted": "04:00:00",
    "remaining_seconds": 14400,
    "formatted_remaining": "04:00",
    "limit_reached": false
}
```

### GET `/work-sessions/history?days=7`
Get work history (last N days)
```json
Response:
{
    "success": true,
    "history": {
        "2025-11-11": {
            "total_seconds": 28800,
            "sessions": [...]
        }
    },
    "total_sessions": 10
}
```

## Frontend Implementation

### JavaScript File: `/public/js/work-timer.js`

**Key Functions:**
- `startWork(taskId)` - Start timer dan create session
- `stopWork()` - Stop timer dan save duration ke backend
- `startCounting()` - Mulai interval counting setiap detik
- `stopCounting()` - Stop interval
- `saveToStorage()` - Save state ke localStorage
- `loadFromStorage()` - Load state dari localStorage
- `formatTime(seconds)` - Format seconds ke HH:MM:SS
- `updateUI()` - Update button visibility dan displays
- `checkActiveSession()` - Check active session saat page load

**LocalStorage Structure:**
```json
{
    "sessionId": 1,
    "taskId": 123,
    "startTime": 1699699200000,
    "elapsedSeconds": 3600
}
```

### UI Components (my-tasks.blade.php)

**Timer Display:**
```html
<!-- Current Session Timer -->
<div id="timer-container">
    <div id="timer-display">00:00:00</div>
</div>

<!-- Today's Total -->
<div id="today-total-display">00:00:00</div>

<!-- Remaining Time -->
<span id="remaining-time-display">08:00</span>

<!-- Control Buttons -->
<button id="start-work-btn">Start Work</button>
<button id="stop-work-btn">Stop Work</button>
```

## Backend Implementation

### Controller: `WorkSessionController`

**Methods:**
- `startWork(Request $request)` - Create new session, validate daily limit
- `stopWork(Request $request)` - Update session with duration from frontend
- `getActiveSession()` - Get current active session
- `getTodayTotal()` - Calculate today's total work time
- `getHistory(Request $request)` - Get work history

### Model: `WorkSession`

**Attributes:**
- `formatted_duration` - Get duration as HH:MM:SS
- `duration_hours` - Get duration in decimal hours

**Scopes:**
- `active()` - Filter active sessions
- `forDate($date)` - Filter by specific date
- `forUser($userId)` - Filter by user

**Relations:**
- `user()` - belongsTo User
- `task()` - belongsTo Task

## Dashboard Integration

### User Dashboard (`dashboard/user.blade.php`)

**Displays:**
1. Today's work time with progress bar (0-8 hours)
2. This week's total hours
3. Daily average
4. Quick link to start working

**Data from Controller:**
```php
$todayFormatted = "04:30"; // HH:MM format
$timeSpent = 18000; // seconds (this week)
```

## Workflow

### Starting Work
1. User klik "Start Work" button
2. Frontend call `/work-sessions/start`
3. Backend check:
   - No active session exists
   - Today's total < 8 hours
4. Backend create new session with status 'active'
5. Frontend save session to localStorage
6. Frontend start timer interval (counting every second)
7. UI updated: show timer, hide start button, show stop button

### During Work
1. Timer counts up every second
2. State saved to localStorage every second
3. Display updated (HH:MM:SS format)
4. If browser refresh, timer resume from localStorage

### Stopping Work
1. User klik "Stop Work" button
2. Frontend stop interval counting
3. Frontend call `/work-sessions/stop` with:
   - session_id
   - duration_seconds (from timer)
4. Backend update session:
   - stopped_at = now()
   - duration_seconds = from request
   - status = 'completed'
5. Frontend clear localStorage
6. UI updated: hide timer, show start button, hide stop button
7. Update today's total display

### Daily Limit Check
1. Before starting new session, check today's total
2. If total >= 28800 seconds (8 hours), prevent start
3. Show notification: "Daily work limit reached"
4. Timer auto-stop at 8 hours if still running

## Error Handling

### Common Errors:
1. **Already has active session** - User trying to start when timer already running
2. **Daily limit reached** - User worked 8+ hours today
3. **Session not found** - Session_id invalid or expired
4. **Network error** - Failed to communicate with backend

### Error Responses:
```json
{
    "success": false,
    "message": "Error description"
}
```

## Testing Checklist

- [ ] Start work session creates database record
- [ ] Timer counts up correctly
- [ ] Timer persists after browser refresh
- [ ] Stop work saves duration to database
- [ ] Daily limit prevents starting after 8 hours
- [ ] Today's total displays correctly
- [ ] Weekly summary calculates correctly
- [ ] Multiple sessions in one day accumulate correctly
- [ ] Timer auto-stops at 8 hours
- [ ] UI updates correctly (buttons, displays)

## Future Enhancements

1. **Task-specific tracking** - Associate sessions with specific tasks
2. **Pause/Resume** - Allow pausing without stopping (status: paused)
3. **Break reminders** - Notify user to take breaks
4. **Weekly reports** - Detailed breakdown by day/task
5. **Export functionality** - Export time logs to CSV/PDF
6. **Admin dashboard** - View all users' work time
7. **Overtime tracking** - Track hours beyond 8/day
8. **Project-based tracking** - Group sessions by project

## Notes

- Timer runs in browser memory (not server-side)
- Database only stores final duration when stopped
- 8-hour limit is enforced both frontend and backend
- LocalStorage ensures timer survives page refresh
- Session validation on both client and server side
