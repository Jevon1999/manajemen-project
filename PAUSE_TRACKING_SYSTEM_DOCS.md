# Work Session Pause Tracking System

## 📋 Overview

Sistem tracking pause/resume untuk work session yang mengatasi bug di mana timer menunjukkan waktu yang salah setelah user logout dan login kembali.

## 🐛 Bug yang Diperbaiki

### Masalah Sebelumnya:
1. User klik **Start Working** → timer berjalan
2. User klik **Stop** (pause) → timer berhenti
3. User logout dan istirahat 13 jam
4. User login kembali → timer menunjukkan **13 jam** (SALAH!)

### Root Cause:
- Session di database tetap `status = 'active'` dan `stopped_at = NULL`
- Frontend menghitung elapsed time dari `started_at` sampai sekarang
- Tidak ada pembedaan antara **PAUSE** (temporary) dan **STOP** (permanent)
- Tidak ada auto-cleanup untuk stale sessions

## ✨ Solusi Implementasi

### 1. Database Schema Changes

**Migration:** `2025_11_13_154744_add_pause_tracking_to_work_sessions_table.php`

```php
Schema::table('work_sessions', function (Blueprint $table) {
    $table->timestamp('paused_at')->nullable()->after('stopped_at');
    $table->integer('pause_duration')->default(0)->after('paused_at')
        ->comment('Total pause duration in seconds');
});
```

**Kolom Baru:**
- `paused_at` → Timestamp saat session di-pause
- `pause_duration` → Total durasi pause dalam detik

### 2. Backend Logic (WorkSessionController)

#### A. Perbedaan PAUSE vs STOP

```php
// PAUSE - Temporary (keep session active)
POST /work-sessions/stop
{
    "session_id": 123,
    "duration_seconds": 3600,
    "action": "pause"  // ← KEY DIFFERENCE
}

// STOP - Permanent (complete session)
POST /work-sessions/stop
{
    "session_id": 123,
    "duration_seconds": 3600,
    "action": "stop"   // ← KEY DIFFERENCE
}
```

#### B. stopWork() - Updated

```php
public function stopWork(Request $request)
{
    // Validate action: pause or stop
    $request->validate([
        'action' => 'required|in:pause,stop'
    ]);
    
    // Check for stale sessions (> 24 hours)
    $sessionAge = Carbon::parse($session->started_at)
        ->diffInHours(Carbon::now('Asia/Jakarta'));
    
    if ($sessionAge >= 24) {
        // Auto-cancel stale session
        $session->status = 'cancelled';
        $session->notes = 'Auto-cancelled: Session older than 24 hours';
        return response()->json([
            'success' => false,
            'stale_session' => true,
            'action' => 'reset'
        ], 410); // 410 Gone
    }
    
    if ($request->action === 'pause') {
        // PAUSE: Keep active, mark paused_at
        $session->paused_at = Carbon::now('Asia/Jakarta');
        $session->duration_seconds = $request->duration_seconds;
        // status tetap 'active'
    } else {
        // STOP: Complete session
        $session->stopped_at = Carbon::now('Asia/Jakarta');
        $session->duration_seconds = $request->duration_seconds;
        $session->status = 'completed';
        $session->paused_at = null;
    }
    
    $session->save();
}
```

#### C. resumeWork() - New Method

```php
public function resumeWork(Request $request)
{
    // Find paused session
    $session = WorkSession::where('user_id', $user->user_id)
        ->where('status', 'active')
        ->whereNotNull('paused_at')
        ->first();
    
    if (!$session) {
        return response()->json([
            'success' => false,
            'message' => 'No paused session found'
        ], 404);
    }
    
    // Check for stale sessions
    $sessionAge = Carbon::parse($session->started_at)
        ->diffInHours(Carbon::now('Asia/Jakarta'));
    
    if ($sessionAge >= 24) {
        // Auto-cancel stale session
        $session->status = 'cancelled';
        $session->notes = 'Auto-cancelled: Stale session';
        $session->save();
        
        return response()->json([
            'success' => false,
            'stale_session' => true,
            'action' => 'reset'
        ], 410);
    }
    
    // Calculate pause duration
    $pauseDuration = Carbon::parse($session->paused_at)
        ->diffInSeconds(Carbon::now('Asia/Jakarta'));
    
    // Update session
    $session->pause_duration += $pauseDuration;
    $session->paused_at = null; // Clear pause flag
    $session->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Work session resumed',
        'session' => $session
    ]);
}
```

#### D. getActiveSession() - Stale Detection

```php
public function getActiveSession()
{
    $session = WorkSession::where('user_id', $user->user_id)
        ->where('status', 'active')
        ->first();
    
    if ($session) {
        // Check for stale sessions (> 24 hours)
        $sessionAge = Carbon::parse($session->started_at)
            ->diffInHours(Carbon::now('Asia/Jakarta'));
        
        if ($sessionAge >= 24) {
            // Auto-cancel stale session
            $session->stopped_at = Carbon::now('Asia/Jakarta');
            $session->duration_seconds = 0;
            $session->status = 'cancelled';
            $session->notes = 'Auto-cancelled: Stale session';
            $session->save();
            
            return response()->json([
                'success' => true,
                'session' => null,
                'stale_session_cancelled' => true,
                'message' => 'Previous session was cancelled due to being older than 24 hours'
            ]);
        }
    }
    
    return response()->json([
        'success' => true,
        'session' => $session
    ]);
}
```

### 3. Frontend Updates (work-timer.js)

#### A. New Properties

```javascript
class WorkTimer {
    constructor() {
        // Existing properties
        this.sessionId = null;
        this.startTime = null;
        this.elapsedSeconds = 0;
        
        // NEW: Pause tracking
        this.isPaused = false;
        this.pausedAt = null;
    }
}
```

#### B. Three Actions Instead of Two

**Before:**
- Start Work
- Stop Work (pause)

**After:**
- Start Work
- Pause Work (temporary, keep session alive)
- Resume Work (continue paused session)
- Stop Work (permanent, complete session)

#### C. pauseWork() Method

```javascript
async pauseWork() {
    // Stop counting
    this.stopCounting();
    
    // Send pause action to backend
    const response = await fetch('/work-sessions/stop', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            session_id: this.sessionId,
            duration_seconds: this.elapsedSeconds,
            action: 'pause' // ← PAUSE instead of STOP
        })
    });
    
    const data = await response.json();
    
    if (data.stale_session) {
        this.showNotification('Session expired. Please start a new one.', 'warning');
        this.reset();
        this.updateUI();
        return false;
    }
    
    // Mark as paused (keep session data)
    this.isPaused = true;
    this.pausedAt = Date.now();
    this.saveToStorage();
    this.updateUI();
    
    return true;
}
```

#### D. resumePausedWork() Method

```javascript
async resumePausedWork() {
    if (!this.sessionId || !this.isPaused) {
        this.showNotification('No paused session found', 'warning');
        return false;
    }
    
    // Resume on backend
    const response = await fetch('/work-sessions/resume', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    const data = await response.json();
    
    if (data.stale_session) {
        this.showNotification('Session expired. Please start a new one.', 'warning');
        this.reset();
        this.updateUI();
        return false;
    }
    
    // Resume counting
    this.isPaused = false;
    this.pausedAt = null;
    this.startCounting();
    this.saveToStorage();
    this.updateUI();
    
    return true;
}
```

#### E. Stale Session Detection on Load

```javascript
async verifySessionAndResume(totalElapsed) {
    const response = await fetch('/work-sessions/active');
    const data = await response.json();
    
    // Check for stale session alert
    if (data.stale_session_cancelled) {
        this.showNotification(data.message, 'warning');
        this.reset();
        this.updateUI();
        return;
    }
    
    if (data.session) {
        // Check session age (24 hours = 86400 seconds)
        if (totalElapsed >= 86400) {
            this.showNotification(
                'Your previous session expired (> 24 hours). Please start a new session.',
                'warning'
            );
            this.reset();
            this.updateUI();
            return;
        }
        
        // Check if session is paused
        if (data.session.paused_at) {
            this.isPaused = true;
            this.pausedAt = new Date(data.session.paused_at).getTime();
            this.elapsedSeconds = data.session.duration_seconds;
            this.updateUI();
        } else {
            // Resume active session
            this.elapsedSeconds = totalElapsed;
            this.startCounting();
            this.updateUI();
        }
    } else {
        // No active session on server
        this.reset();
        this.updateUI();
    }
}
```

#### F. Updated UI Logic

```javascript
updateUI() {
    const startBtn = document.getElementById('start-work-btn');
    const stopBtn = document.getElementById('stop-work-btn');
    const pauseBtn = document.getElementById('pause-work-btn');
    const resumeBtn = document.getElementById('resume-work-btn');
    
    if (this.sessionId) {
        if (this.isPaused) {
            // Session is PAUSED
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';   // Can still stop completely
            pauseBtn.style.display = 'none';
            resumeBtn.style.display = 'inline-flex'; // Show resume button
        } else if (this.timerInterval) {
            // Session is RUNNING
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';
            pauseBtn.style.display = 'inline-flex';  // Show pause button
            resumeBtn.style.display = 'none';
        }
    } else {
        // No active session
        startBtn.style.display = 'inline-flex';
        stopBtn.style.display = 'none';
        pauseBtn.style.display = 'none';
        resumeBtn.style.display = 'none';
    }
}
```

### 4. Auto-Cleanup Command

**File:** `app/Console/Commands/CleanupStaleSessions.php`

**Command:** `php artisan work:cleanup-stale-sessions`

**Fungsi:** Auto-cancel work sessions yang masih active tapi sudah > 24 jam

```php
public function handle()
{
    $threshold = Carbon::now('Asia/Jakarta')->subHours(24);
    
    $staleSessions = WorkSession::where('status', 'active')
        ->where('started_at', '<', $threshold)
        ->get();
    
    foreach ($staleSessions as $session) {
        $ageInHours = Carbon::parse($session->started_at)
            ->diffInHours(Carbon::now('Asia/Jakarta'));
        
        // Auto-cancel the session
        $session->stopped_at = Carbon::now('Asia/Jakarta');
        $session->duration_seconds = 0; // Invalid session
        $session->status = 'cancelled';
        $session->notes = "Auto-cancelled: Session was active for {$ageInHours} hours";
        $session->save();
    }
}
```

**Scheduled:** Hourly (setiap jam)

```php
// app/Console/Kernel.php
$schedule->command('work:cleanup-stale-sessions')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->runInBackground();
```

### 5. Routes

```php
// routes/web.php
Route::prefix('work-sessions')->name('work-sessions.')->group(function () {
    Route::post('/start', [WorkSessionController::class, 'startWork'])->name('start');
    Route::post('/stop', [WorkSessionController::class, 'stopWork'])->name('stop');
    Route::post('/resume', [WorkSessionController::class, 'resumeWork'])->name('resume'); // NEW
    Route::get('/active', [WorkSessionController::class, 'getActiveSession'])->name('active');
    Route::get('/today-total', [WorkSessionController::class, 'getTodayTotal'])->name('today-total');
    Route::get('/history', [WorkSessionController::class, 'getHistory'])->name('history');
});
```

## 🎯 User Flow

### Scenario 1: Normal Work Session

1. User klik **Start Working** → `POST /work-sessions/start`
   - Backend creates session: `status = 'active'`, `started_at = now()`
   - Frontend starts timer

2. User klik **Pause** → `POST /work-sessions/stop` dengan `action=pause`
   - Backend sets: `paused_at = now()`, status tetap `'active'`
   - Frontend stops timer, shows "Resume" button

3. User klik **Resume** → `POST /work-sessions/resume`
   - Backend calculates pause duration, clears `paused_at`
   - Frontend resumes timer

4. User klik **Stop** → `POST /work-sessions/stop` dengan `action=stop`
   - Backend sets: `stopped_at = now()`, `status = 'completed'`
   - Frontend resets session

### Scenario 2: Stale Session (Bug Fix)

1. User starts working at **10:00**
2. User pauses at **11:00** (worked 1 hour)
3. User closes browser and goes home
4. User returns **next day at 10:00** (24 hours later)
5. User opens app → Frontend calls `GET /work-sessions/active`

**Backend Response:**
```json
{
    "success": true,
    "session": null,
    "stale_session_cancelled": true,
    "message": "Previous session was cancelled due to being older than 24 hours"
}
```

6. Frontend shows notification and resets timer
7. User can start a fresh session

### Scenario 3: Auto-Cleanup

**Hourly Cron Job:**
```bash
php artisan work:cleanup-stale-sessions
```

**Output:**
```
Checking for stale work sessions...
Cancelled session #123 (User ID: 5, Age: 25h)
Cancelled session #124 (User ID: 8, Age: 48h)
✓ Successfully cancelled 2 stale session(s).
```

## 📊 Scheduled Tasks

Verifikasi dengan:
```bash
php artisan schedule:list
```

**Output:**
```
0 0 * * *  php artisan work:reset-daily ................... Next Due: 8 hours from now
0 * * * *  php artisan work:cleanup-stale-sessions ....... Next Due: 3 minutes from now
0 8 * * *  php artisan tasks:check-deadlines .............. Next Due: 16 hours from now
```

## 🧪 Testing

### Test 1: Normal Pause/Resume

```bash
# 1. Start work session
curl -X POST http://localhost:8000/work-sessions/start \
  -H "Content-Type: application/json" \
  -d '{"task_id": 123}'

# 2. Pause after 5 minutes
curl -X POST http://localhost:8000/work-sessions/stop \
  -H "Content-Type: application/json" \
  -d '{"session_id": 456, "duration_seconds": 300, "action": "pause"}'

# 3. Resume
curl -X POST http://localhost:8000/work-sessions/resume \
  -H "Content-Type: application/json"

# 4. Stop permanently
curl -X POST http://localhost:8000/work-sessions/stop \
  -H "Content-Type: application/json" \
  -d '{"session_id": 456, "duration_seconds": 600, "action": "stop"}'
```

### Test 2: Stale Session Detection

```sql
-- Simulate old session (update started_at to 25 hours ago)
UPDATE work_sessions 
SET started_at = NOW() - INTERVAL 25 HOUR
WHERE session_id = 123;

-- Check active session (should auto-cancel)
curl http://localhost:8000/work-sessions/active
```

### Test 3: Auto-Cleanup Command

```bash
# Run cleanup manually
php artisan work:cleanup-stale-sessions

# Expected output:
# Checking for stale work sessions...
# Cancelled session #123 (User ID: 5, Age: 25h)
# ✓ Successfully cancelled 1 stale session(s).
```

## 📝 Database Schema

```sql
CREATE TABLE work_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    task_id INT NULL,
    started_at TIMESTAMP NOT NULL,
    stopped_at TIMESTAMP NULL,
    paused_at TIMESTAMP NULL,           -- NEW: When session was paused
    pause_duration INT DEFAULT 0,       -- NEW: Total pause time in seconds
    duration_seconds INT DEFAULT 0,
    work_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🔍 Key Improvements

### Before:
❌ Timer menunjukkan 13 jam setelah logout/login  
❌ Tidak ada pembedaan pause vs stop  
❌ Stale sessions tidak pernah dibersihkan  
❌ Frontend menghitung elapsed time tanpa validasi  

### After:
✅ Timer akurat dengan validasi stale session  
✅ Jelas membedakan pause (temporary) vs stop (permanent)  
✅ Auto-cleanup setiap jam untuk stale sessions  
✅ Frontend validasi session age sebelum resume  
✅ Backend mendeteksi dan cancel stale sessions otomatis  

## 🚀 Deployment Checklist

- [x] Migration applied: `2025_11_13_154744_add_pause_tracking_to_work_sessions_table`
- [x] Model updated: `WorkSession.php` (fillable + casts)
- [x] Controller updated: `WorkSessionController.php` (stopWork, resumeWork, getActiveSession)
- [x] Routes registered: `POST /work-sessions/resume`
- [x] Frontend updated: `work-timer.js` (pauseWork, resumePausedWork)
- [x] Command created: `CleanupStaleSessions.php`
- [x] Scheduled task registered: `work:cleanup-stale-sessions` (hourly)
- [x] Documentation created: `PAUSE_TRACKING_SYSTEM_DOCS.md`

## 📞 Support

Jika menemukan bug atau ada pertanyaan:
1. Check `storage/logs/laravel.log` untuk backend errors
2. Check browser console untuk frontend errors
3. Test command manually: `php artisan work:cleanup-stale-sessions`
4. Verify scheduled tasks: `php artisan schedule:list`

---

**Last Updated:** 2025-11-13  
**Version:** 1.0.0  
**Status:** ✅ Deployed & Tested
