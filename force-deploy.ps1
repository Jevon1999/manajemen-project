# Force Deploy Script - Update server manually
# Run this if webhook fails

Write-Host "🚀 Force Deploy to Production Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Server details (update these with your actual server info)
$SERVER = "root@jevonbintang.my.id"
$PROJECT_PATH = "/var/www/manajemen_project"

Write-Host "📡 Connecting to server: $SERVER" -ForegroundColor Yellow
Write-Host ""

# SSH command to update server
$SSH_COMMAND = @"
cd $PROJECT_PATH && \
echo '📥 Pulling latest changes...' && \
sudo -u www-data git fetch --all && \
sudo -u www-data git reset --hard origin/master && \
echo '🧹 Clearing Laravel cache...' && \
php artisan cache:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan optimize:clear && \
echo '♻️ Restarting services...' && \
sudo systemctl restart php8.2-fpm && \
sudo systemctl restart nginx && \
echo '✅ Deploy completed!' && \
echo 'Latest commit:' && \
git log -1 --oneline
"@

Write-Host "Executing deployment commands..." -ForegroundColor Green
ssh $SERVER $SSH_COMMAND

Write-Host ""
Write-Host "✅ Deployment script finished!" -ForegroundColor Green
Write-Host "🌐 Check: https://jevonbintang.my.id/admin/projects" -ForegroundColor Cyan
Write-Host ""
Write-Host "⚠️ Don't forget to clear browser cache (Ctrl+Shift+R)" -ForegroundColor Yellow
