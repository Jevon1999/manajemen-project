# TODO & Improvement Plan

## Sistem & Fungsionalitas

### 1. Validasi Route & Navigasi
- [ ] Pastikan semua route di sidebar dan dashboard valid dan terdaftar di `routes/web.php`.
- [ ] Ganti semua route yang tidak ada (misal: `teams.members`) dengan route yang benar.

### 2. Role-Based Access
- [ ] Sidebar dan menu hanya tampil sesuai role user (admin, leader, user).
- [ ] Validasi permission di backend, bukan hanya di tampilan.

### 3. Optimasi Query & Data
- [ ] Pindahkan query role user dari Blade ke Controller/Model agar lebih efisien.
- [ ] Gunakan Eloquent relationship untuk akses data role dan anggota tim.

### 4. UI/UX Sidebar
- [ ] Hapus dropdown yang tidak dipakai, ganti dengan link langsung.
- [ ] Tambahkan responsif dan aksesibilitas (Tailwind, aria-label).
- [ ] Konsistensi warna, icon, dan badge role.

### 5. Error Handling
- [ ] Tampilkan pesan error yang jelas jika route tidak ditemukan.
- [ ] Logging error navigasi dan permission.

### 6. Security
- [ ] Pastikan menu admin hanya tampil untuk admin.
- [ ] Validasi semua aksi penting di backend.

### 7. Maintenance & Backup
- [ ] Implementasi fitur backup/restore database dan file.
- [ ] Tambahkan menu utilities untuk admin (sudah mulai dibuat).

### 8. Reporting & Analytics
- [ ] Tambahkan fitur laporan dan analitik untuk admin dan leader.
- [ ] Export data ke Excel/PDF.

### 9. Activity Log
- [ ] Implementasi log aktivitas user untuk audit dan keamanan.

### 10. Dokumentasi
- [ ] Update README dan dokumentasi fitur baru.

---

## Rencana Implementasi
1. Audit dan perbaiki semua route sidebar/dashboard.
2. Refactor pengecekan role dan permission ke backend.
3. Optimasi query dan relasi Eloquent.
4. Update tampilan sidebar agar lebih sederhana dan responsif.
5. Tambahkan fitur backup/restore dan utilities admin.
6. Implementasi reporting, export, dan activity log.
7. Update dokumentasi dan README.

## Tampilan
1. Buat tampilan menjadi lebih modern dan enak diliat
2. Terapkan sistem middlewere yang baik dan benar
3. Buat urutan route menjadi berurutan. jadi ketika baru membuka diarahkan ke route login terlebih dahulu

# Sistem
1. Buat sistem menjadi berurutan dan kongkrit
2. Gunakan prinsip coding agar mudah untuk maintance
3. Berikan Command di setiap fitur yang telah dibuat

---

*Update sesuai progress dan feedback user.*
