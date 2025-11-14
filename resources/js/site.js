// Small UI enhancements (sidebar collapse, theme toggle)
document.addEventListener('DOMContentLoaded', function () {
    // Sidebar collapse feature removed: sidebar is always visible. Previously removed toggle and persisted state handling.

    // Small floating action button for quick create on small screens
    const fab = document.getElementById('fab-create-project');
    if (fab) {
        fab.addEventListener('click', function () {
            const open = window.openCreateProjectModal || (() => {});
            open();
        });
    }

    // Animate mini progress bars (set via data-width attribute)
    const miniFills = document.querySelectorAll('.mini-bar-fill');
    miniFills.forEach(function(el) {
        const w = el.getAttribute('data-width') || el.style.width || '0%';
        // small timeout for nicer entrance
        setTimeout(() => { el.style.width = w; }, 120);
    });

    // Sidebar click feedback
    const sidebarLinks = document.querySelectorAll('aside a');
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            link.classList.add('sidebar-item-pressed');
            setTimeout(() => link.classList.remove('sidebar-item-pressed'), 300);
        });
    });

    // Initialize small Chart.js sparklines using bundled Chart.js (via Vite)
        try {
            // import Chart dynamically so it's tree-shaken by Vite in production builds
            if (window.dashboardData) {
                import('chart.js/auto').then(({ default: Chart }) => {
                    const ctxUsers = document.getElementById('chart-users');
                    const ctxOverdue = document.getElementById('chart-overdue');

                    const accent1 = getComputedStyle(document.documentElement).getPropertyValue('--accent-400') || '#60a5fa';
                    const accent2 = getComputedStyle(document.documentElement).getPropertyValue('--accent-500') || '#7c3aed';

                    if (ctxUsers) {
                        new Chart(ctxUsers.getContext('2d'), {
                            type: 'line',
                            data: { labels: window.dashboardData.usersSeries.map((_,i) => i+1), datasets: [{ data: window.dashboardData.usersSeries, borderColor: accent1.trim(), backgroundColor: 'transparent', tension: 0.3, pointRadius: 0 }] },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true } }, scales: { x: { display: false }, y: { display: false } } }
                        });
                    }

                    if (ctxOverdue) {
                        new Chart(ctxOverdue.getContext('2d'), {
                            type: 'bar',
                            data: { labels: window.dashboardData.overdueSeries.map((_,i) => i+1), datasets: [{ data: window.dashboardData.overdueSeries, backgroundColor: accent2.trim() }] },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true } }, scales: { x: { display: false }, y: { display: false } } }
                        });
                    }
                }).catch(err => console.warn('Chart import/init failed', err));
            }
        } catch (err) {
            console.warn('Chart init error', err);
        }
});

export {};
