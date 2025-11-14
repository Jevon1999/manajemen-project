# 🚀 Auto Deploy Scripts

Scripts untuk otomatis pull perubahan dari GitHub ke server production.

## 📁 Available Scripts

### 1. `deploy-simple.sh` - Basic Auto Deploy
Script sederhana untuk pull dan deploy perubahan dari GitHub.

**Fitur:**
- ✅ Auto fetch & pull dari GitHub
- ✅ Check for updates (skip jika sudah up to date)
- ✅ Composer install dependencies
- ✅ Run migrations
- ✅ Clear cache

**Usage:**
```bash
# Upload ke server
scp deploy-simple.sh user@server:/var/www/manajemen_project/

# Set executable
chmod +x /var/www/manajemen_project/deploy-simple.sh

# Run
cd /var/www/manajemen_project
./deploy-simple.sh
```

---

### 2. `deploy-advanced.sh` - Advanced with Backup & Rollback
Script lengkap dengan backup otomatis dan rollback jika deploy gagal.

**Fitur:**
- ✅ Auto backup files & database
- ✅ Maintenance mode during deployment
- ✅ Auto rollback on failure
- ✅ Detailed logging with colors
- ✅ Permission fix
- ✅ Old backup cleanup (keep 5 latest)
- ✅ Error handling

**Usage:**
```bash
# Upload ke server
scp deploy-advanced.sh user@server:/var/www/manajemen_project/

# Set executable
chmod +x /var/www/manajemen_project/deploy-advanced.sh

# Run
cd /var/www/manajemen_project
./deploy-advanced.sh
```

---

## ⚙️ Configuration

Edit script sesuai kebutuhan:

```bash
# Configuration variables
PROJECT_PATH="/var/www/manajemen_project"  # Path project di server
BRANCH="master"                             # Branch yang akan di-pull
BACKUP_DIR="/var/backups/manajemen_project" # Lokasi backup (advanced only)
```

---

## 🔄 Auto Deploy dengan Cron

Untuk auto deploy berkala (misal setiap 5 menit):

```bash
# Edit crontab
crontab -e

# Auto deploy setiap 5 menit
*/5 * * * * /var/www/manajemen_project/deploy-simple.sh >> /var/log/deploy.log 2>&1

# Atau setiap jam
0 * * * * /var/www/manajemen_project/deploy-advanced.sh >> /var/log/deploy.log 2>&1
```

---

## 📝 Logs

**Simple script:**
- Output langsung ke terminal

**Advanced script:**
- Colored output dengan status (INFO, SUCCESS, WARNING, ERROR)
- Redirect ke file: `./deploy-advanced.sh >> /var/log/deploy.log 2>&1`

---

## 🔒 Security Notes

1. **SSH Keys:** Setup SSH keys untuk git pull tanpa password
   ```bash
   ssh-keygen -t ed25519 -C "server@email.com"
   cat ~/.ssh/id_ed25519.pub
   # Add ke GitHub: Settings > SSH Keys
   ```

2. **File Permissions:** Script harus executable
   ```bash
   chmod +x deploy-*.sh
   ```

3. **Database Backup:** Advanced script backup database otomatis (butuh MySQL access)

---

## 🆘 Troubleshooting

**Git pull error?**
```bash
# Check git remote
git remote -v

# Check SSH connection
ssh -T git@github.com

# Reset if needed
git reset --hard origin/master
```

**Permission denied?**
```bash
# Fix ownership
chown -R www-data:www-data /var/www/manajemen_project

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

**Composer error?**
```bash
# Update composer
composer self-update

# Clear composer cache
composer clear-cache
```

---

## 📊 Deploy Flow

### Simple Script:
```
1. Navigate to project
2. Check git status
3. Fetch from GitHub
4. Check for updates
5. Pull changes
6. Composer install
7. Run migrations
8. Clear cache
✓ Done!
```

### Advanced Script:
```
1. Navigate to project
2. Check git status
3. Fetch from GitHub
4. Check for updates
5. Create backup (files + database)
6. Enable maintenance mode
7. Stash local changes
8. Pull changes (rollback on fail)
9. Composer install (rollback on fail)
10. Run migrations (rollback on fail)
11. Clear cache
12. Disable maintenance mode
13. Set permissions
14. Cleanup old backups
✓ Done!
```

---

## 🎯 Best Practices

1. **Test dulu di staging server**
2. **Backup manual sebelum deploy besar**
3. **Monitor logs** untuk detect error
4. **Setup notification** untuk deploy success/fail
5. **Gunakan advanced script** untuk production

---

## 📦 Requirements

- Git installed
- Composer installed
- PHP 8.1+
- MySQL/MariaDB (for database backup)
- rsync (for file backup)
- SSH access to server

---

Created: November 15, 2025
Repository: https://github.com/Jevon1999/manajemen-project
