# Fitur Register - ProjectHub

## 📋 Ringkasan
Fitur register sudah diimplementasikan dengan lengkap dan otomatis mengatur role user baru sebagai **'user'**.

## ✅ Komponen yang Sudah Ada

### 1. Routes (`routes/web.php`)
```php
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
```

### 2. Controller (`app/Http/Controllers/Auth/AuthController.php`)
- **Method**: `register(RegisterRequest $request)`
- **Fungsi**: 
  - Membuat user baru dengan role otomatis **'user'**
  - Generate username unik dari nama
  - Hash password
  - Auto login setelah register
  - Redirect ke dashboard

### 3. Request Validation (`app/Http/Requests/Auth/RegisterRequest.php`)
**Validasi:**
- `name`: Required, string, max 255 karakter
- `email`: Required, email valid, unique
- `password`: Required, minimal 8 karakter, harus konfirmasi
- `password_confirmation`: Required, harus sama dengan password

### 4. View (`resources/views/Auth/register.blade.php`)
**Fitur UI:**
- Modern design dengan gradient
- Form fields: Full Name, Email, Password, Confirm Password
- Checkbox Terms & Conditions
- Toggle password visibility
- Social login buttons (Google & GitHub)
- Responsive design
- AOS animations

### 5. Database Schema (`users` table)
```sql
- user_id (Primary Key)
- username (unique)
- full_name
- email (unique)
- password (hashed)
- role (enum: 'admin', 'leader', 'user') - DEFAULT: 'user'
- status (enum: 'active', 'inactive') - DEFAULT: 'active'
- current_task_status (enum: 'idle', 'working') - DEFAULT: 'idle'
- timestamps
```

## 🎯 Cara Menggunakan

### 1. Akses Halaman Register
```
URL: http://localhost:8000/register
```

### 2. Isi Form Register
- **Full Name**: Nama lengkap user
- **Email**: Email valid dan belum terdaftar
- **Password**: Minimal 8 karakter
- **Confirm Password**: Harus sama dengan password
- **Checkbox**: Setujui Terms & Conditions

### 3. Submit Form
- Klik tombol "Create Account"
- Sistem akan:
  1. Validasi input
  2. Generate username unik dari nama
  3. Hash password
  4. Simpan ke database dengan role = 'user'
  5. Auto login
  6. Redirect ke dashboard

## 🔐 Keamanan
- Password di-hash menggunakan bcrypt
- CSRF protection aktif
- Session regeneration setelah register
- Email unique validation
- Status 'active' by default

## 📝 Contoh Data User Baru
```php
[
    'username' => 'johndoe',        // Auto-generated
    'full_name' => 'John Doe',      // From form
    'email' => 'john@example.com',   // From form
    'password' => '$2y$10$...',      // Hashed
    'role' => 'user',                // OTOMATIS
    'status' => 'active',            // Default
    'current_task_status' => 'idle'  // Default
]
```

## 🚀 Testing Register

### Manual Test:
1. Buka browser: `http://localhost:8000/register`
2. Isi form dengan data valid
3. Submit
4. Pastikan:
   - User tersimpan di database dengan role 'user'
   - Auto login berhasil
   - Redirect ke dashboard

### Database Check:
```sql
SELECT user_id, username, full_name, email, role, status 
FROM users 
ORDER BY created_at DESC 
LIMIT 1;
```

## ✨ Fitur Tambahan

### Username Auto-Generate
Jika nama "John Doe" sudah ada username "johndoe", sistem akan generate:
- johndoe1
- johndoe2
- dst...

### Social Login (Optional)
- Google OAuth
- GitHub OAuth
(Perlu konfigurasi di `.env`)

## 🎨 UI Features
- Modern gradient design
- Responsive layout
- Password visibility toggle
- Form validation dengan pesan error
- Loading animations (AOS)
- Social login buttons

## 📌 Catatan Penting
✅ **Role otomatis 'user'** - Tidak perlu input manual
✅ **Status otomatis 'active'** - User langsung bisa login
✅ **Auto login** - Setelah register langsung masuk
✅ **Username unik** - Auto-generated dari nama
✅ **Password secure** - Bcrypt hash

## 🔄 Flow Register
```
User mengisi form register
    ↓
Submit form
    ↓
Validasi input (RegisterRequest)
    ↓
Generate username unik
    ↓
Hash password
    ↓
Simpan ke database (role = 'user')
    ↓
Auto login user
    ↓
Redirect ke dashboard dengan pesan sukses
```

## 🎉 Selesai!
Fitur register sudah siap digunakan dengan role otomatis sebagai 'user'.
