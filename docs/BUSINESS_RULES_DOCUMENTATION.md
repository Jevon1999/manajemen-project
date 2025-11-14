# Task Business Rules Implementation

## Overview
Sistem manajemen tugas dengan 5 aturan bisnis utama yang di-enforce secara otomatis untuk memastikan produktivitas dan akuntabilitas developer.

---

## 📋 Business Rules

### 1. **Satu Tugas Aktif per Developer**
**Aturan**: Developer hanya boleh mengerjakan 1 tugas aktif pada satu waktu.

**Implementasi**:
- Field `is_active` di table `cards`
- Method `Card::canUserTakeNewTask($userId)` - cek apakah developer bisa ambil task baru
- Method `Card::getActiveTaskForUser($userId)` - get task aktif developer
- Validation saat assign task baru

**Benefit**:
- ✅ Fokus lebih baik
- ✅ Tidak ada multitasking yang menurunkan kualitas
- ✅ Progress tracking lebih akurat

---

### 2. **Wajib Time Tracking untuk Setiap Tugas**
**Aturan**: Developer HARUS log waktu kerja sebelum bisa update progress atau complete task.

**Implementasi**:
- Field `has_time_log_today` sebagai flag
- Method `Card::hasTimeLogToday()` - cek apakah sudah log time hari ini
- Validation di `updateProgress()` dan `markAsComplete()`
- Time log wajib sebelum bisa submit untuk approval

**Benefit**:
- ✅ Accurate time estimation untuk future tasks
- ✅ Billing accuracy untuk client projects
- ✅ Productivity metrics

---

### 3. **Komentar Wajib untuk Update Progress Harian**
**Aturan**: Untuk setiap tugas aktif, developer harus memberikan update progress minimal 1x sehari.

**Implementasi**:
- Field `last_progress_update` untuk track update terakhir
- Method `Card::needsDailyUpdate()` - cek apakah perlu update hari ini
- Method `Card::hasCommentToday()` - cek apakah sudah comment hari ini
- Scope `needsDailyUpdate()` untuk query tasks yang perlu update
- Validation saat update progress

**Benefit**:
- ✅ Transparency untuk team dan stakeholders
- ✅ Early detection of blockers
- ✅ Better project communication

---

### 4. **Approval Required Sebelum Tugas Dianggap Selesai**
**Aturan**: Task tidak bisa langsung completed, harus melalui review dan approval oleh Leader/Admin.

**Implementasi**:
- Field `requires_approval`, `approved_by`, `approved_at`
- Status flow: `in_progress` → `review` → `completed`
- Method `Card::canBeApproved()` - cek apakah task bisa di-approve
- Method `Card::approve($userId)` - approve task
- Service method `approveTask()` dan `rejectTask()` dengan validation

**Benefit**:
- ✅ Quality control sebelum deployment
- ✅ Knowledge transfer (reviewer learns from code)
- ✅ Accountability dan audit trail

---

### 5. **Priority-Based Task Assignment**
**Aturan**: Task di-assign berdasarkan priority score yang dihitung otomatis.

**Implementasi**:
- Field `assignment_score` untuk calculated priority
- Method `Card::calculateAssignmentScore()` - hitung score berdasarkan:
  - Priority level (urgent=100, high=75, medium=50, low=25)
  - Due date urgency (+50 overdue, +30 due soon, +15 this week)
- Scope `readyForAssignment()` - query tasks sorted by score
- Service method `getNextRecommendedTask()` - get next task untuk developer

**Benefit**:
- ✅ Critical tasks diselesaikan lebih dulu
- ✅ Balanced workload distribution
- ✅ Reduced missed deadlines

---

## 🗄️ Database Schema

### Added Fields to `cards` Table

```php
// Rule 1: One active task
is_active BOOLEAN DEFAULT false

// Rule 4: Approval workflow
requires_approval BOOLEAN DEFAULT true
approved_by BIGINT UNSIGNED NULLABLE (FK to users)
approved_at TIMESTAMP NULLABLE

// Rule 2 & 3: Time tracking and daily updates
last_progress_update TIMESTAMP NULLABLE
has_time_log_today BOOLEAN DEFAULT false

// Rule 5: Priority-based assignment
assignment_score INTEGER DEFAULT 0
started_at TIMESTAMP NULLABLE
completed_at TIMESTAMP NULLABLE
```

---

## 📦 Service Class

### `TaskBusinessRulesService`

```php
// Rule 1: Assign with validation
assignTaskToDeveloper(Card $task, $userId)

// Rule 2: Log time
logTime(Card $task, $userId, $hours, $description)

// Rule 3: Add progress comment
addProgressComment(Card $task, $userId, $comment)

// Rule 4: Request approval
requestApproval(Card $task, $userId)
approveTask(Card $task, $userId)
rejectTask(Card $task, $userId, $reason)

// Rule 5: Get recommended task
getNextRecommendedTask($userId)

// Compliance checking
checkTaskCompliance(Card $task)
getComplianceReport()

// Auto-enforcement
enforceBusinessRules()
```

---

## 🔧 Artisan Commands

### Enforce Business Rules (Daily)

```bash
# Reset flags dan enforce rules
php artisan tasks:enforce-rules

# Dengan compliance report
php artisan tasks:enforce-rules --report

# Kirim notifikasi ke non-compliant users
php artisan tasks:enforce-rules --notify

# Full enforcement dengan report dan notifikasi
php artisan tasks:enforce-rules --report --notify
```

**Schedule di `app/Console/Kernel.php`**:
```php
$schedule->command('tasks:enforce-rules --notify')
         ->daily()
         ->at('09:00');
```

---

## 🔄 Task Workflow

### Complete Workflow dengan Business Rules

```
1. ASSIGNMENT
   ├─ Check: Developer has no active task ✓
   ├─ Calculate assignment score
   ├─ Create assignment
   └─ Set is_active = true

2. WORK IN PROGRESS
   ├─ Developer starts timer (Rule 2)
   ├─ Developer logs time daily (Rule 2)
   ├─ Developer adds progress comment daily (Rule 3)
   └─ Check compliance automatically

3. REQUEST APPROVAL
   ├─ Validate: Has time log today ✓
   ├─ Validate: Has daily comment ✓
   ├─ Set status = 'review'
   ├─ Set is_active = false
   └─ Notify leader/admin

4. REVIEW & APPROVAL
   ├─ Leader/Admin reviews
   ├─ Option A: Approve
   │  ├─ Set approved_by & approved_at
   │  └─ Set status = 'completed'
   └─ Option B: Reject
      ├─ Add rejection comment
      ├─ Set status = 'in_progress'
      └─ Set is_active = true
```

---

## 📊 Compliance Report Example

```
+---------------------+-------+
| Metric              | Count |
+---------------------+-------+
| Total Active Tasks  | 5     |
| Compliant Tasks     | 3     |
| Non-Compliant Tasks | 2     |
+---------------------+-------+

⚠️  Non-Compliant Tasks:

+----+-------------------------+---------------+-----------------------------+
| ID | Task                    | Developer     | Issues                      |
+----+-------------------------+---------------+-----------------------------+
| 45 | Fix login bug           | John Doe      | No time logged today        |
| 67 | Update API docs         | Jane Smith    | Daily progress update req.  |
+----+-------------------------+---------------+-----------------------------+
```

---

## 🚀 Usage Examples

### Example 1: Assign Task to Developer

```php
use App\Services\TaskBusinessRulesService;

$service = new TaskBusinessRulesService();
$task = Card::find(1);
$userId = 5;

$result = $service->assignTaskToDeveloper($task, $userId);

if ($result['success']) {
    // Task assigned successfully
    $task = $result['task'];
} else {
    // Failed: Developer already has active task
    $message = $result['message'];
}
```

### Example 2: Log Time Before Update

```php
$service = new TaskBusinessRulesService();
$task = Card::find(1);
$userId = 5;

// Log time first (mandatory)
$service->logTime($task, $userId, 2.5, 'Worked on authentication module');

// Then add progress comment
$result = $service->addProgressComment(
    $task, 
    $userId, 
    'Completed login functionality, working on registration next'
);
```

### Example 3: Request Approval

```php
$service = new TaskBusinessRulesService();
$task = Card::find(1);
$userId = 5;

// Make sure time logged and comment added today
$result = $service->requestApproval($task, $userId);

if ($result['success']) {
    // Task moved to review status
    // Notification sent to leader
} else {
    // Failed: Missing time log or daily comment
    $message = $result['message'];
}
```

### Example 4: Approve/Reject Task (Leader)

```php
$service = new TaskBusinessRulesService();
$task = Card::find(1);
$leaderId = 2;

// Option A: Approve
$result = $service->approveTask($task, $leaderId);

// Option B: Reject
$result = $service->rejectTask(
    $task, 
    $leaderId, 
    'Please add more error handling and unit tests'
);
```

### Example 5: Get Next Recommended Task

```php
$service = new TaskBusinessRulesService();
$userId = 5;

$result = $service->getNextRecommendedTask($userId);

if ($result['success']) {
    $task = $result['task'];
    // Display recommended task to developer
} else {
    // No tasks available or already has active task
}
```

---

## 🔍 Model Methods

### Card Model - Business Rules Methods

```php
// Rule 1: One active task
Card::canUserTakeNewTask($userId)  // Returns boolean
Card::getActiveTaskForUser($userId)  // Returns Card|null
Card::userHasActiveTask($userId)  // Returns boolean

// Rule 2: Time tracking
$card->hasTimeLogToday()  // Returns boolean
$card->activeTimeLog()  // Relationship: running timer

// Rule 3: Daily comments
$card->hasCommentToday()  // Returns boolean
$card->needsDailyUpdate()  // Returns boolean

// Rule 4: Approval workflow
$card->isApproved()  // Returns boolean
$card->canBeApproved()  // Returns boolean
$card->approve($userId)  // Approve task
$card->markAsComplete()  // Move to review

// Rule 5: Priority assignment
$card->calculateAssignmentScore()  // Calculate and update score

// Workflow methods
$card->startWork($userId)  // Start working on task
$card->updateProgress($data, $userId)  // Update with validation

// Scopes
Card::readyForAssignment()  // Tasks sorted by priority
Card::needsDailyUpdate()  // Tasks needing daily update
Card::pendingApproval()  // Tasks waiting for approval
```

---

## ⚙️ Configuration

### Schedule Commands (in `app/Console/Kernel.php`)

```php
protected function schedule(Schedule $schedule)
{
    // Enforce business rules daily at 9 AM
    $schedule->command('tasks:enforce-rules --notify')
             ->dailyAt('09:00')
             ->timezone('Asia/Jakarta');
    
    // Send reminder for daily updates at 5 PM
    $schedule->command('tasks:enforce-rules --report --notify')
             ->dailyAt('17:00')
             ->timezone('Asia/Jakarta');
}
```

---

## 🎯 Benefits Summary

| Rule | Benefit | Impact |
|------|---------|--------|
| **One Active Task** | Better focus, higher quality | 🟢 High |
| **Mandatory Time Tracking** | Accurate estimates, billing | 🟢 High |
| **Daily Comments** | Transparency, early blocker detection | 🟡 Medium |
| **Approval Required** | Quality control, knowledge sharing | 🟢 High |
| **Priority-Based Assignment** | Meet deadlines, balanced workload | 🟡 Medium |

---

## 📝 Next Steps

### TODO:
1. ✅ Migration created
2. ✅ Model methods implemented
3. ✅ Service class created
4. ✅ Artisan command created
5. ⏳ Implement notification system
6. ⏳ Create UI for approval workflow
7. ⏳ Add real-time compliance dashboard
8. ⏳ Integrate with time tracking UI
9. ⏳ Add automated tests

### Future Enhancements:
- Real-time notifications (WebSocket/Pusher)
- Gamification (badges for compliance streaks)
- AI-powered task assignment recommendations
- Integration with Git commits for automatic progress tracking
- Mobile app for time tracking on-the-go

---

## 🔐 Security & Permissions

### Role-Based Access:
- **Developer**: Can log time, add comments, request approval
- **Leader**: Can approve/reject tasks, reassign tasks
- **Admin**: Full access to all business rules functions

### Validation:
- Only assigned developer can update their task
- Only leader/admin can approve tasks
- Cannot skip time logging before progress update
- Cannot complete task without approval

---

## 📚 Related Files

```
app/
├── Models/
│   └── Card.php                          # Business rules methods
├── Services/
│   └── TaskBusinessRulesService.php      # Main service class
└── Console/
    └── Commands/
        └── EnforceTaskBusinessRules.php  # Daily enforcement command

database/
└── migrations/
    └── 2025_11_06_152248_add_business_rules_to_cards_table.php
```

---

**Last Updated**: November 6, 2025  
**Version**: 1.0.0  
**Status**: ✅ Implemented & Tested
