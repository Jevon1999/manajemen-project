# Extension to Review Flow - Bug Fix Documentation

## 🐛 Bug Description

**Issue:** User tidak bisa menyelesaikan task (klik "Selesaikan" button) setelah extension request di-approve oleh leader.

**Expected Flow:**
```
overdue → extension_request → (approved) → in_progress → review → done
```

**Actual Flow:**
```
overdue → extension_request → (approved) → in_progress → ❌ STUCK (tidak bisa ke review)
```

## 🔍 Root Cause Analysis

### 1. **Unblock Timing Issue**
- Extension approval melakukan `unblock()` SETELAH update status
- Ini bisa menyebabkan race condition atau stale data

### 2. **Status Check Terlalu Ketat**
- Code hanya update status jika `status === 'overdue'`
- Jika task sudah `in_progress` sebelum overdue, tidak akan di-handle
- Task tetap blocked meskipun seharusnya sudah di-unblock

### 3. **Missing Fresh Data**
- `BoardTransitionService::transitionToReview()` tidak reload data terbaru
- Bisa membaca `is_blocked = true` meskipun sudah di-unblock

## ✅ Fixes Applied

### Fix 1: Reorder Unblock Logic
**File:** `app/Http/Controllers/ExtensionRequestController.php`

**Before:**
```php
// Update deadline
$entity->update([...]);

// Change status
if ($entity->status === 'overdue') {
    $entity->update(['status' => 'in_progress']);
}

// Unblock (TOO LATE!)
$entity->unblock();
```

**After:**
```php
// Update deadline
$entity->update([...]);

// Unblock FIRST
$entity->unblock();

// Change status (support more cases)
if ($entity->status === 'overdue' || $entity->status === 'todo') {
    $entity->update(['status' => 'in_progress']);
}
// If already in_progress, keep it (but it's now unblocked)
```

### Fix 2: Add Data Refresh
**File:** `app/Services/BoardTransitionService.php`

**Before:**
```php
public function transitionToReview(Task $task, int $userId): array
{
    // Check if task is blocked
    if ($task->is_blocked) {
        return ['success' => false, ...];
    }
    ...
}
```

**After:**
```php
public function transitionToReview(Task $task, int $userId): array
{
    // Reload task to get fresh data
    $task->refresh();
    
    // Check if task is blocked
    if ($task->is_blocked) {
        return ['success' => false, ...];
    }
    ...
}
```

### Fix 3: Enhanced Logging
Added detailed logging at critical points:
- Extension approval process
- Unblock confirmation
- Status transition attempts
- Validation failures

## 🧪 Testing Steps

### Test Case 1: Normal Extension Flow
1. Create task with deadline in the past (overdue)
2. Task should show as blocked
3. User requests extension with valid reason
4. Leader approves extension
5. ✅ Task should be unblocked and status = `in_progress`
6. User stops any running timers
7. User clicks "Selesaikan"
8. ✅ Task should transition to `review`

### Test Case 2: Already In-Progress Task
1. Create task, start working (status = `in_progress`)
2. Deadline passes → task becomes blocked
3. User requests extension
4. Leader approves
5. ✅ Task should remain `in_progress` but unblocked
6. User stops timer
7. User clicks "Selesaikan"
8. ✅ Task should transition to `review`

### Test Case 3: Multiple Extension Requests
1. Task overdue, request extension
2. Leader rejects first request
3. Task remains blocked
4. User requests extension again
5. Leader approves second request
6. ✅ Task should be unblocked
7. Complete task
8. ✅ Should transition to review

## 📊 Status Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    EXTENSION FLOW                            │
└─────────────────────────────────────────────────────────────┘

   [TODO]
      │
      ▼
   [IN_PROGRESS] ──────► ⏱️ Timer Started
      │
      │ (deadline passes)
      ▼
   [OVERDUE] ◄────────► 🚫 BLOCKED
      │
      │ (user requests extension)
      ▼
   [extension_request]
      │
      ├──► APPROVED ──────┐
      │                   │
      │                   ▼
      │              🔓 UNBLOCK
      │                   │
      │                   ▼
      │            [IN_PROGRESS] ──────► ✅ Ready to Complete
      │                   │
      └──► REJECTED       ▼
             │        [REVIEW] ──────► Leader Approval
             │            │
             ▼            ▼
         Stay BLOCKED  [DONE]
```

## 🔍 Validation Checklist

After extension approval, task must satisfy:
- ✅ `is_blocked = false`
- ✅ `block_reason = null`
- ✅ `status = 'in_progress'` (or remains in_progress)
- ✅ `deadline = new_deadline`
- ✅ No running timers for the user

## 📝 Related Files Modified

1. `app/Http/Controllers/ExtensionRequestController.php`
   - Reordered unblock logic
   - Added logging
   - Fixed status update conditions

2. `app/Services/BoardTransitionService.php`
   - Added `$task->refresh()` before validation
   - Enhanced logging for debugging
   - Better error messages

3. `test_extension_to_review.php`
   - Test script to verify the fix

## 🚀 Deployment Notes

### Before Deploy:
- ✅ Backup database
- ✅ Test on staging with real scenarios
- ✅ Check logs for any warnings

### After Deploy:
- Monitor logs for "TransitionToReview attempt" entries
- Check for any "Task is blocked" warnings
- Verify extension approval logs show "is_blocked_after: false"

### Rollback Plan:
If issues occur:
```bash
git revert <commit-hash>
php artisan optimize:clear
```

## 🎯 Success Metrics

- ✅ 0 "Task is blocked" errors after approved extensions
- ✅ 100% successful review transitions after extension approval
- ✅ Proper log entries showing unblock → status change flow
- ✅ No user complaints about stuck tasks

## 📞 Support

If issues persist:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Search for: "TransitionToReview attempt"
3. Verify task state with test script: `php test_extension_to_review.php`
4. Check database directly:
   ```sql
   SELECT task_id, status, is_blocked, block_reason 
   FROM tasks 
   WHERE assigned_to = [user_id]
   AND status = 'in_progress';
   ```
