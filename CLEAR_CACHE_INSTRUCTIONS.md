# 🧹 Clear Cache Instructions

## If You See Alpine.js Errors (activeTab is not defined)

This error occurs when your browser cache still has old Alpine.js code. Follow these steps:

### **For End Users (Browser Cache):**

#### **Chrome/Edge:**
```
1. Open: https://jevonbintang.my.id
2. Press: Ctrl + Shift + Delete
3. Select: "Cached images and files"
4. Time range: "All time"
5. Click: "Clear data"
6. Refresh page: Ctrl + F5
```

#### **Firefox:**
```
1. Press: Ctrl + Shift + Delete
2. Select: "Cache"
3. Click: "Clear Now"
4. Refresh: Ctrl + F5
```

#### **Safari:**
```
1. Press: Cmd + Option + E
2. Refresh: Cmd + R
```

---

### **For Server (Laravel Cache):**

```bash
# SSH to server
ssh user@jevonbintang.my.id

# Navigate to project
cd /var/www/manajemen_project

# Clear ALL cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Rebuild optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

### **Quick Fix (Force Refresh):**

On any page, press:
- **Windows/Linux**: `Ctrl + Shift + R` or `Ctrl + F5`
- **Mac**: `Cmd + Shift + R`

This bypasses cache and loads fresh files.

---

## Why This Happens?

The Edit Project modal was recently migrated from **Alpine.js** to **Vue.js**:

**Before (Alpine):**
```blade
<div x-data="{ activeTab: 'basic' }">
    <button @click="activeTab = 'settings'">
```

**After (Vue):**
```javascript
data() {
    return {
        activeTab: 'basic'
    }
}
```

Old cached HTML still has `x-data` and Alpine expressions, but new code uses Vue.

---

## Verification:

After clearing cache, check:
1. ✅ Edit Project modal opens without errors
2. ✅ Can switch between "Info Dasar" and "Pengaturan" tabs
3. ✅ No console errors about `activeTab`

---

## Still Having Issues?

Run deploy script to ensure latest code:
```bash
cd /var/www/manajemen_project
./deploy-advanced.sh
```

Or manual:
```bash
git pull origin master
php artisan optimize:clear
php artisan optimize
sudo systemctl reload php8.2-fpm
```
