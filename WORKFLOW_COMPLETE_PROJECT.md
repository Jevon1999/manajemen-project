# 📋 Workflow: Menyelesaikan Project

## 🎯 Overview
System sudah memiliki fitur lengkap untuk menyelesaikan project dengan tracking keterlambatan otomatis. Berikut adalah workflow lengkapnya.

---

## 🔄 Workflow untuk Leader

### 1️⃣ **Akses Project Detail**
```
Menu: Leader Panel → My Projects → Click Project Card
URL: /leader/projects/{project_id}
```

### 2️⃣ **Klik Tombol "Tandai Selesai"**
- **Lokasi**: Header halaman, sebelah tombol "Create Task"
- **Warna**: Hijau (Green button)
- **Icon**: ✓ Checkmark
- **Kondisi**: Hanya muncul jika project status ≠ 'completed'

### 3️⃣ **Modal Completion Muncul**
Modal akan menampilkan:

#### **A. Jika Project ON TIME** (≤ deadline):
```
✅ Selesaikan Project

📝 Catatan Penyelesaian (Optional)
├─ Textarea untuk dokumentasi
└─ Placeholder: "Tambahkan catatan mengenai penyelesaian project..."

[Batal]  [✅ Selesaikan]
```

#### **B. Jika Project OVERDUE** (> deadline):
```
⚠️ Selesaikan Project (Terlambat)

⚠️ Project Terlambat
├─ Deadline: 17 Nov 2025
├─ Hari ini: 18 Nov 2025
└─ Terlambat: 1 hari

📝 Catatan Penyelesaian (Optional)
└─ Textarea untuk dokumentasi

⚠️ Alasan Keterlambatan * (REQUIRED)
├─ Textarea wajib diisi
└─ Placeholder: "Jelaskan alasan project terlambat diselesaikan..."

[Batal]  [⚠️ Selesaikan]
```

### 4️⃣ **Submit Form**
- **On Time**: Completion notes optional
- **Overdue**: Delay reason REQUIRED (form validation)
- **Action**: POST to `/leader/projects/{id}/complete`

### 5️⃣ **System Processing**
Backend akan:
1. ✅ Set project status = 'completed'
2. ✅ Set completed_at = now()
3. ✅ Calculate is_overdue (deadline vs completed_at)
4. ✅ Calculate delay_days (jika overdue)
5. ✅ Save completion_notes
6. ✅ Save delay_reason (jika overdue)
7. ✅ Send notifications ke admin & team

### 6️⃣ **Success Response**
**Jika On Time:**
```
"Project berhasil diselesaikan tepat waktu!"
```

**Jika Late:**
```
"Project berhasil diselesaikan dengan keterlambatan X hari"
```

### 7️⃣ **Post-Completion State**
- ✅ Tombol berubah: "Tandai Selesai" → "Buka Kembali"
- ✅ Badge status: Active → Completed
- ✅ Muncul di "Completed Projects" dashboard
- ✅ Tidak bisa edit/create task lagi

---

## 👀 Workflow untuk Admin

### 1️⃣ **View Completed Projects**
**Cara 1: Admin Dashboard Statistics**
```
Dashboard → Statistics Cards
├─ Completed Projects (total)
├─ Completed On Time (green)
└─ Late Completion (red)
```

**Cara 2: Admin Projects - Completed Tab**
```
Admin → Projects → Tab: Completed
```

### 2️⃣ **Completed Projects Table View**
Tabel menampilkan:
```
┌───────────────┬────────┬──────────┬────────────┬────────────────┬──────────────┬─────────┐
│ Project       │ Leader │ Deadline │ Completed  │ Status         │ Notes        │ Actions │
├───────────────┼────────┼──────────┼────────────┼────────────────┼──────────────┼─────────┤
│ Project A     │ John   │ 17 Nov   │ 17 Nov     │ 🟢 On Time    │ "Success!"   │ 👁 View │
│ Testing       │ Jevon  │ 16 Nov   │ 18 Nov     │ 🔴 Late (2d)  │ "Resource…"  │ 👁 View │
│ Mobile App    │ Sarah  │ 15 Nov   │ 16 Nov     │ 🟡 Late (1d)  │ "Client…"    │ 👁 View │
└───────────────┴────────┴──────────┴────────────┴────────────────┴──────────────┴─────────┘
```

**Status Badge Colors:**
- 🟢 **Green**: On Time (completed ≤ deadline)
- 🟡 **Yellow**: Late 1-6 days
- 🔴 **Red**: Late ≥ 7 days

### 3️⃣ **View Completion Details**
Click "View" → Project Detail Page
- ✅ Full completion info
- ✅ Completion notes
- ✅ Delay reason (if applicable)
- ✅ Delay days calculation
- ✅ Timeline history

---

## 🎨 Visual Guide

### Button States
```css
/* Before Completion */
Button: "Tandai Selesai"
Color: Green (#10B981)
Icon: ✓ Checkmark

/* After Completion */
Button: "Buka Kembali"
Color: Yellow (#F59E0B)
Icon: ↻ Reopen
```

### Badge Colors
```css
/* On Time */
Badge: "On Time"
Background: bg-green-100
Text: text-green-800

/* Late < 7 days */
Badge: "Late (X days)"
Background: bg-yellow-100
Text: text-yellow-800

/* Late ≥ 7 days */
Badge: "Late (X days)"
Background: bg-red-100
Text: text-red-800
```

---

## 📊 Database Schema

### Projects Table Fields Used:
```sql
status          VARCHAR     -- 'active' → 'completed'
completed_at    TIMESTAMP   -- Auto-set on completion
deadline        DATE        -- Reference for overdue check
is_overdue      BOOLEAN     -- Auto-calculated
delay_days      INTEGER     -- Auto-calculated (deadline - completed_at)
completion_notes TEXT       -- Leader input (optional)
delay_reason    TEXT        -- Leader input (required if overdue)
```

---

## 🔧 Technical Implementation

### Routes
```php
// Complete project
POST /leader/projects/{id}/complete
Controller: ProjectLeaderController@complete

// Reopen project
POST /projects/{id}/reopen
Controller: ProjectLeaderController@reopen
```

### Controller Logic (ProjectLeaderController@complete)
```php
public function complete(Request $request, $projectId)
{
    // 1. Validate
    $validated = $request->validate([
        'completion_notes' => 'nullable|string',
        'delay_reason' => 'required_if:is_overdue,1|nullable|string',
    ]);

    // 2. Mark as completed (model method)
    $project->markAsCompleted(
        $request->completion_notes,
        $request->delay_reason
    );

    // 3. Return success message
    return redirect()->back()->with('success', $message);
}
```

### Model Method (Project@markAsCompleted)
```php
public function markAsCompleted($notes = null, $delayReason = null)
{
    $this->status = 'completed';
    $this->completed_at = now();
    $this->completion_notes = $notes;
    
    // Auto-calculate overdue
    if ($this->deadline && $this->completed_at > $this->deadline) {
        $this->is_overdue = true;
        $this->delay_days = $this->completed_at->diffInDays($this->deadline);
        $this->delay_reason = $delayReason;
    } else {
        $this->is_overdue = false;
        $this->delay_days = 0;
    }
    
    $this->save();
}
```

---

## 🚨 Common Issues & Solutions

### Issue 1: Tombol "Tandai Selesai" Tidak Muncul
**Penyebab:**
- File belum ter-pull di VPS
- Cache browser

**Solusi:**
```bash
# Di VPS
cd /var/www/manajemen_project
sudo -u www-data git pull origin master
php artisan view:clear
php artisan cache:clear

# Di Browser
Ctrl + Shift + R (hard refresh)
```

### Issue 2: Modal Tidak Muncul
**Penyebab:**
- Alpine.js tidak load
- JavaScript error

**Solusi:**
1. Check browser console (F12)
2. Pastikan Alpine.js CDN load
3. Clear browser cache

### Issue 3: Form Submit Error "Delay reason required"
**Penyebab:**
- Project overdue tapi delay_reason kosong

**Solusi:**
- Isi field "Alasan Keterlambatan" (mandatory untuk overdue)

### Issue 4: Completed Projects Tidak Muncul di Dashboard
**Penyebab:**
- Migration belum jalan di VPS
- Field is_overdue, delay_days belum ada

**Solusi:**
```bash
# Di VPS
php artisan migrate

# Check if columns exist
php artisan tinker
>>> Schema::hasColumn('projects', 'is_overdue')
>>> Schema::hasColumn('projects', 'delay_days')
```

---

## ✅ Deployment Checklist

Sebelum testing di production:

- [ ] **Code Pushed to GitHub**
  ```bash
  git push origin master
  ```

- [ ] **Pull di VPS**
  ```bash
  ssh jevonbintang.my.id
  cd /var/www/manajemen_project
  sudo -u www-data git pull origin master
  ```

- [ ] **Run Migration**
  ```bash
  php artisan migrate
  ```

- [ ] **Clear Cache**
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  ```

- [ ] **Verify Migration Success**
  ```bash
  php artisan tinker
  >>> Schema::hasColumn('projects', 'delay_days')
  >>> Schema::hasColumn('projects', 'completion_notes')
  ```

- [ ] **Test Workflow**
  - [ ] Login sebagai leader
  - [ ] Buka project detail
  - [ ] Klik "Tandai Selesai"
  - [ ] Modal muncul dengan form
  - [ ] Submit form berhasil
  - [ ] Project status = completed
  - [ ] Muncul di completed dashboard

---

## 📸 Screenshot Locations

### 1. Project Detail dengan Tombol "Tandai Selesai"
- URL: `/leader/projects/{id}`
- Location: Header, kanan atas

### 2. Modal Completion (On Time)
- Trigger: Click "Tandai Selesai" button
- Fields: Completion notes (optional)

### 3. Modal Completion (Overdue)
- Trigger: Click "Tandai Selesai" button pada overdue project
- Fields: 
  - Warning box (yellow)
  - Completion notes (optional)
  - Delay reason (required)

### 4. Leader Dashboard - Completed Section
- URL: `/leader/dashboard`
- Location: Below statistics cards
- Shows: List of recently completed projects

### 5. Admin Projects - Completed Tab
- URL: `/admin/projects`
- Location: Tab navigation
- Shows: Detailed table view

---

## 🎓 Best Practices

### Untuk Leader:
1. ✅ **Selalu isi completion notes** - Dokumentasi penting untuk review
2. ✅ **Jika overdue, jelaskan detail** - Transparency untuk improvement
3. ✅ **Complete project setelah semua task done** - Quality assurance
4. ✅ **Review timeline sebelum complete** - Avoid premature completion

### Untuk Admin:
1. ✅ **Review completed projects weekly** - Track team performance
2. ✅ **Analyze delay patterns** - Identify bottlenecks
3. ✅ **Export data untuk reporting** - Documentation
4. ✅ **Follow up on late completions** - Process improvement

---

## 📞 Support

**Jika ada masalah:**
1. Check browser console (F12)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify migration ran successfully
4. Clear all caches
5. Hard refresh browser (Ctrl + Shift + R)

**Files yang terlibat:**
- `resources/views/leader/projects/show.blade.php` - Project detail view
- `app/Http/Controllers/ProjectLeaderController.php` - Complete logic
- `app/Models/Project.php` - Model methods
- `database/migrations/*_add_completion_tracking_to_projects_table.php` - Schema

---

## 🔗 Related Documentation
- [PROJECT_COMPLETION_TRACKING.md](./PROJECT_COMPLETION_TRACKING.md)
- [LEADER_DASHBOARD_README.md](./LEADER_DASHBOARD_README.md)
- [BUSINESS_RULES_DOCUMENTATION.md](./BUSINESS_RULES_DOCUMENTATION.md)

---

**Last Updated:** 17 November 2025
**Version:** 1.0
**Status:** ✅ Production Ready
