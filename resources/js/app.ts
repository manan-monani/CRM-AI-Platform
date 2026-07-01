import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

import { wTrans } from './Core/i18n';
import { formatMoney } from './Core/money';
import * as admin from './routes/admin';
import * as guest from './routes/index';

import * as auth from './routes/auth';
import * as customer from './routes/customer';
import * as legal from './routes/legal';
import * as locale from './routes/locale';

// Helper to prefix all wayfinder routes with the base path
const prefixRoutes = (routes: any, prefix: string) => {
    Object.keys(routes).forEach(key => {
        const item = routes[key];
        if (item && (typeof item === 'object' || typeof item === 'function')) {
            if (item.definition && typeof item.definition.url === 'string' && !item.definition.url.startsWith(prefix)) {
                item.definition.url = prefix + item.definition.url;
            }
            // Recurse for nested route groups and the functions themselves (which have properties like .form)
            prefixRoutes(item, prefix);
        }
    });
};

// Detect base path dynamically based on the current URL
let basePath = window.location.pathname.startsWith('/Admin-Boilerplate-AI-Develop/public')
    ? '/Admin-Boilerplate-AI-Develop/public'
    : '';

// Ensure strict equality for empty base path to avoid double slashes if logic changes
if (basePath === '/') basePath = '';
prefixRoutes(admin, basePath);
prefixRoutes(guest, basePath);
prefixRoutes(auth, basePath);
prefixRoutes(customer, basePath);
prefixRoutes(legal, basePath);
prefixRoutes(locale, basePath);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.config.globalProperties.__ = wTrans;
        app.config.globalProperties.trans = wTrans;
        app.config.globalProperties.$money = formatMoney;

        app.use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
