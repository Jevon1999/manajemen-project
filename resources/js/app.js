import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// Simple route helper
window.route = function(name, params) {
    const routes = {
        'login': '/',
        'login.post': '/login',
        'register': '/register',
        'register.post': '/register',
        'dashboard': '/dashboard'
    };
    return routes[name] || '/';
};

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});

// Initialize AOS after DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    // Import AOS dynamically to avoid build issues
    import('aos').then(AOS => {
        AOS.default.init({
            duration: 800,
            once: true,
        });
    }).catch(err => {
        console.warn('AOS tidak dapat dimuat:', err);
    });
});

// Site specific enhancements (sidebar toggle, small helpers)
import './site';
