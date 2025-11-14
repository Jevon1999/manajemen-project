# Quick Start Guide - Testing Notification System

## Prerequisites
- ✅ XAMPP/MySQL running
- ✅ Database `manajemen_project` exists
- ✅ Laravel app configured (`.env` file)

## Step-by-Step Testing

### 1. Run Migration (Jika Belum)
```bash
cd C:\UKK\manajemen_project
php artisan migrate
```

Output expected:
```
Migration table created successfully.
Migrating: 2025_01_07_000001_create_notifications_table
Migrated:  2025_01_07_000001_create_notifications_table
```

### 2. Start Laravel Server
```bash
php artisan serve
```

### 3. Login ke Aplikasi
1. Buka browser: `http://localhost:8000`
2. Login dengan user yang ada (admin/leader/user)

### 4. Cek Notification Bell
1. Lihat icon bell di navbar (kanan atas)
2. Pastikan ada badge count jika ada notifikasi
3. Klik bell untuk buka dropdown

### 5. Test Manual Notification (via Tinker)

Open Laravel Tinker:
```bash
php artisan tinker
```

Send test notification:
```php
// Get user dan task
$user = \App\Models\User::first();
$task = \App\Models\Card::first();

// Send time log reminder
$user->notify(new \App\Notifications\TimeLogReminderNotification($task));

// Check di database
\DB::table('notifications')->where('notifiable_id', $user->id)->get();

// Exit tinker
exit;
```

### 6. Cek di UI
1. Refresh browser
2. Notification bell sekarang ada badge "1"
3. Klik bell, lihat notification muncul
4. Klik "Log Waktu Sekarang", redirect ke task page
5. Notification otomatis marked as read

### 7. Test More Notifications

#### Daily Comment Reminder:
```php
php artisan tinker

$user = \App\Models\User::first();
$task = \App\Models\Card::first();
$user->notify(new \App\Notifications\DailyCommentReminderNotification($task));

exit;
```

#### Overdue Task:
```php
php artisan tinker

$user = \App\Models\User::first();
$task = \App\Models\Card::first();
$user->notify(new \App\Notifications\OverdueTaskNotification($task));

exit;
```

#### Task Approval Pending (untuk leader/admin):
```php
php artisan tinker

$leader = \App\Models\User::where('role', 'leader')->first();
$task = \App\Models\Card::first();
$submitter = \App\Models\User::where('role', 'user')->first();

$leader->notify(new \App\Notifications\TaskApprovalPendingNotification($task, $submitter));

exit;
```

#### Task Status Changed:
```php
php artisan tinker

$user = \App\Models\User::first();
$task = \App\Models\Card::first();
$admin = \App\Models\User::where('role', 'admin')->first();

$user->notify(new \App\Notifications\TaskStatusChangedNotification($task, 'approved', $admin));

exit;
```

### 8. Test Batch Notifications (Command)

**Note**: Pastikan ada active tasks di database dengan assigned users

```bash
# Run enforcement command
php artisan tasks:enforce-rules --report --notify
```

Output expected:
```
🔧 Enforcing Task Business Rules...

📋 Resetting daily flags...
   ✓ Reset 5 time log flags
   ✓ Flagged 2 overdue tasks

📊 Generating Compliance Report...
+---------------------+-------+
| Metric              | Count |
+---------------------+-------+
| Total Active Tasks  | 5     |
| Compliant Tasks     | 3     |
| Non-Compliant Tasks | 2     |
+---------------------+-------+

📧 Sending compliance notifications...
📊 Total notifications sent: 2

✅ Business rules enforcement completed!
```

### 9. Test Notification Index Page
1. Buka `http://localhost:8000/notifications`
2. Lihat semua notifikasi dengan pagination
3. Test button "Tandai Semua Dibaca"
4. Test button "Hapus yang Sudah Dibaca"
5. Test delete individual notification

### 10. Test Mark as Read API
Open browser console:
```javascript
// Mark notification as read
fetch('/notifications/{notification-id-here}/read', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

### 11. Test Mark All as Read
```javascript
fetch('/notifications/mark-all-read', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

## Expected Results

### ✅ Notification Bell Component
- [ ] Bell icon visible di navbar
- [ ] Badge count accurate (matches unread count)
- [ ] Dropdown opens on click
- [ ] Shows 10 most recent notifications
- [ ] Correct icons & colors per notification type
- [ ] Relative timestamps ("5 menit yang lalu")
- [ ] "Tandai Semua Dibaca" button works
- [ ] Empty state shows when no notifications

### ✅ Notification Dropdown Items
- [ ] Title displayed correctly
- [ ] Message displayed correctly
- [ ] Action button visible
- [ ] Click redirects to task page
- [ ] Auto mark as read on click
- [ ] Badge count updates after mark as read
- [ ] Unread indicator (blue dot) visible for unread

### ✅ Notifications Index Page
- [ ] URL `/notifications` accessible
- [ ] All notifications listed (paginated)
- [ ] Mark all as read works
- [ ] Clear read notifications works
- [ ] Delete individual notification works
- [ ] Priority badges show correctly
- [ ] Action buttons redirect correctly
- [ ] Empty state shows when no notifications

### ✅ API Endpoints
- [ ] GET `/notifications/unread-count` returns JSON with count
- [ ] GET `/notifications/recent` returns JSON with 10 notifications
- [ ] POST `/notifications/{id}/read` marks as read
- [ ] POST `/notifications/mark-all-read` marks all as read
- [ ] DELETE `/notifications/{id}` deletes notification
- [ ] POST `/notifications/clear-read` clears all read

### ✅ Database
- [ ] `notifications` table exists
- [ ] Notifications inserted correctly
- [ ] `read_at` is NULL for unread
- [ ] `read_at` has timestamp for read
- [ ] `data` field contains correct JSON structure

## Common Issues & Solutions

### Issue 1: Badge count tidak muncul
**Solution**: 
- Cek di database ada notifications dengan `read_at = NULL`
- Cek Alpine.js loaded di layout
- Cek `unreadCount` value di x-data

### Issue 2: Dropdown tidak muncul
**Solution**:
- Cek browser console untuk errors
- Cek Alpine.js syntax di component
- Cek `@click.away="open = false"` works

### Issue 3: Notification tidak tersimpan
**Solution**:
- Cek migration run successfully
- Cek User model has `Notifiable` trait
- Cek error logs di `storage/logs/laravel.log`

### Issue 4: Action button tidak redirect
**Solution**:
- Cek `action_url` di notification data
- Cek route exists (e.g., `developer.tasks.show`)
- Cek user has permission to access route

### Issue 5: Mark as read tidak work
**Solution**:
- Cek CSRF token valid
- Cek route `/notifications/{id}/read` accessible
- Cek network tab di browser console
- Cek method POST bukan GET

## Manual Database Check

### Check notifications table:
```sql
SELECT * FROM notifications WHERE notifiable_id = 1 ORDER BY created_at DESC LIMIT 5;
```

### Check unread count:
```sql
SELECT COUNT(*) FROM notifications WHERE notifiable_id = 1 AND read_at IS NULL;
```

### Check notification data:
```sql
SELECT id, type, data, read_at, created_at FROM notifications WHERE notifiable_id = 1;
```

### Mark notification as read manually:
```sql
UPDATE notifications SET read_at = NOW() WHERE id = 'notification-uuid-here';
```

### Delete all notifications for user:
```sql
DELETE FROM notifications WHERE notifiable_id = 1;
```

## Next Steps After Testing

1. ✅ **Verify all notification types work** (5 types)
2. ✅ **Test with multiple users** (different roles)
3. ✅ **Test mark as read functionality** (single & batch)
4. ✅ **Test delete functionality**
5. ✅ **Test pagination** on index page
6. ✅ **Test scheduled command** integration
7. 🔄 **Optional: Add real-time updates** (WebSocket/Pusher)
8. 🔄 **Optional: Add notification preferences** (user settings)

## Success Criteria

✅ All 5 notification types dapat dikirim
✅ Notifications tersimpan di database
✅ Notification bell menampilkan badge count correct
✅ Dropdown menampilkan 10 notifikasi terbaru
✅ Mark as read functionality works (single & batch)
✅ Notifications index page accessible dan functional
✅ Delete notifications works
✅ Integration dengan business rules command works
✅ UI responsive dan user-friendly

---

**Congratulations!** 🎉

Sistem notifikasi in-app sudah berhasil diimplementasikan dan siap digunakan!

Semua notifikasi sekarang masuk ke database dan ditampilkan di UI dengan notification bell yang interaktif.
