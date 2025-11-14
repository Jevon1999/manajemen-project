# Start Work Feature Documentation

## Overview
Fitur Start Work memungkinkan user dengan role **designer** dan **developer** untuk melakukan tracking waktu kerja pada subtask dalam project.

## Key Features

### 🎯 Role-Based Access
- Hanya user dengan role `user` yang memiliki project member role `designer` atau `developer` yang dapat menggunakan fitur ini
- Admin dan Leader tidak dapat menggunakan fitur ini

### ⏱️ Timer Functionality
- **Start Timer**: Memulai pencatatan waktu kerja untuk subtask tertentu
- **Stop Timer**: Menghentikan timer dan menyimpan durasi kerja ke database
- **Single Active Timer**: User hanya dapat menjalankan 1 timer aktif dalam satu waktu

### 🎨 UI/UX Features
- **Gradient Button Design**: Tombol Start Work dengan gradient hijau dan efek animasi pulse
- **Visual Feedback**: Animasi dan notifikasi untuk memberikan feedback kepada user
- **Conditional Display**: Tombol berubah menjadi "Stop Work" ketika timer sedang berjalan

## Technical Implementation

### Files Modified/Created

#### 1. **SubtaskTimerController.php** (New)
```php
Location: app/Http/Controllers/SubtaskTimerController.php
Purpose: Handle API endpoints untuk start/stop timer
Key Methods:
- startTimer() - Memulai timer untuk subtask
- stopTimer() - Menghentikan timer dan menghitung durasi
- canWorkOnSubtask() - Validasi permission user
```

#### 2. **Routes (API & Web)**
```php
// API Routes (routes/api.php)
POST api/v1/tasks/{taskId}/subtasks/{subtaskId}/start-timer
POST api/v1/tasks/{taskId}/subtasks/{subtaskId}/stop-timer

// Web Routes (routes/web.php)  
POST tasks/{task}/subtasks/{subtask}/start-timer
POST tasks/{task}/subtasks/{subtask}/stop-timer
```

#### 3. **Frontend (tasks/show.blade.php)**
```javascript
// JavaScript Functions
startWork(subtaskId) - AJAX call untuk start timer
stopWork(subtaskId) - AJAX call untuk stop timer

// CSS Animations
.pulse-animation - Efek pulse pada tombol
@keyframes pulse-glow - Animasi glow hijau
```

### Database Integration

#### TimeLog Model Updates
- Field `start_time`: Waktu mulai kerja
- Field `end_time`: Waktu selesai kerja  
- Field `duration_seconds`: Total durasi dalam detik
- Field `notes`: Catatan activity (otomatis: "Working on subtask: [nama]")

### Permission System

#### Access Control Matrix
| Role | Project Member Role | Can Use Start Work | Notes |
|------|----|----|-------|
| user | designer | ✅ | Can start/stop timer |
| user | developer | ✅ | Can start/stop timer |
| user | project_manager | ❌ | No access |
| admin | any | ❌ | No access |
| leader | any | ❌ | No access |

#### Validation Rules
1. User harus assigned ke task yang bersangkutan
2. Subtask harus belongs to task tersebut
3. User harus member dari project dengan role designer/developer
4. User hanya boleh punya 1 active timer

### API Response Format

#### Success Response (Start Timer)
```json
{
    "success": true,
    "message": "Timer started successfully",
    "timer_id": 123,
    "start_time": "2024-01-15T10:30:00.000Z"
}
```

#### Success Response (Stop Timer)
```json
{
    "success": true,
    "message": "Timer stopped successfully", 
    "duration_seconds": 3600,
    "timeSpent": "1h 0m",
    "timeSpentMinutes": 60.0
}
```

#### Error Response
```json
{
    "success": false,
    "error": "You do not have permission to work on this subtask"
}
```

### Security Features

#### Input Validation
- Task ID dan Subtask ID validation
- CSRF Token protection
- Authentication middleware required

#### Permission Checks
- Role-based access control
- Project membership validation  
- Task assignment verification
- Active timer conflict prevention

## User Interface

### Visual Design
- **Color Scheme**: Gradient hijau (#10b981 to #059669)
- **Animation**: Pulse glow effect dengan opacity transition
- **Icons**: 🚀 untuk Start Work, ⏰ untuk Stop Work
- **Typography**: Bold text dengan shadow effect

### Responsive Behavior
- Button adapts to container width
- Touch-friendly on mobile devices
- Smooth animations across devices

## Testing Guide

### Manual Testing Steps
1. **Login sebagai user** dengan role `user`
2. **Pastikan user adalah member project** dengan role `designer` atau `developer`
3. **Navigate ke task detail** yang di-assign ke user
4. **Scroll ke subtask section** 
5. **Klik tombol "🚀 START WORK"**
6. **Verify**: Notifikasi muncul, timer dimulai
7. **Klik tombol "⏰ STOP WORK"**
8. **Verify**: Timer dihentikan, durasi tercatat

### Test Cases
- ✅ User dengan permission correct bisa start/stop timer
- ✅ User tanpa permission tidak bisa akses fitur
- ✅ User tidak bisa start timer kedua jika sudah ada active timer
- ✅ Timer data tersimpan correctly di database
- ✅ UI update secara real-time

## Troubleshooting

### Common Issues

#### 1. "No permission" Error
- **Cause**: User bukan designer/developer di project
- **Solution**: Pastikan user memiliki project member role yang benar

#### 2. "Already have active timer" Error  
- **Cause**: User sudah punya timer running di task lain
- **Solution**: Stop timer yang aktif dulu sebelum start yang baru

#### 3. JavaScript Error
- **Cause**: CSRF token missing atau invalid
- **Solution**: Refresh halaman untuk regenerate token

### Development Notes
- Controller menggunakan try-catch untuk error handling
- JavaScript menggunakan async/await untuk better UX
- Database queries di-optimize untuk performance
- Logging untuk debugging (Laravel Log)

## Future Enhancements
- [ ] Real-time timer display dengan countdown
- [ ] Timer history per subtask
- [ ] Time tracking analytics dashboard
- [ ] Mobile app integration
- [ ] Bulk timer operations

---

**Created**: January 2024  
**Version**: 1.0  
**Status**: Production Ready ✅