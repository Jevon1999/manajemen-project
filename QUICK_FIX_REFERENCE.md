### QUICK FIX REFERENCE - Extension to Review Bug

#### PROBLEM
Task stuck di `in_progress` setelah extension approved, tidak bisa ke `review`

#### ROOT CAUSES
1. Unblock dipanggil terlalu lambat
2. Status check terlalu ketat  
3. Stale data di BoardTransitionService

#### FIXES APPLIED
✅ `ExtensionRequestController`: Unblock FIRST, support more status cases
✅ `BoardTransitionService`: Add `$task->refresh()` + enhanced logging

#### TEST
```bash
php test_extension_to_review.php
```

#### VERIFY
```bash
tail -f storage/logs/laravel.log | grep "TransitionToReview"
```

#### EXPECTED LOG OUTPUT
```
TransitionToReview attempt task_id:X status:in_progress is_blocked:false
Entity unblocked is_blocked_after:false
successfully transitioned to review
```

#### QUICK DEBUG
```sql
SELECT task_id, status, is_blocked, block_reason 
FROM tasks WHERE task_id = X;
```

Should show:
- status = `in_progress`
- is_blocked = `0` (false)
- block_reason = `NULL`

#### FILES CHANGED
- app/Http/Controllers/ExtensionRequestController.php
- app/Services/BoardTransitionService.php

#### DOCS
See `EXTENSION_TO_REVIEW_FIX.md` for full details
