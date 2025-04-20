import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import IMaskDirective from './directives/imask'; // Импортируйте вашу директиву
import useNotifications from './Composables/useNotifications';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Регистрация директивы маски
        app.directive('imask', IMaskDirective);

        // Создаем глобальный плагин для уведомлений
        const notificationsService = useNotifications();

        app.config.globalProperties.$notify = {
            success(message, title = 'Успех', timeout = 4000) {
                return notificationsService.showNotification(message, 'mgreen', title, timeout);
            },
            error(message, title = 'Ошибка', timeout = 4000) {
                return notificationsService.showNotification(message, 'mred', title, timeout);
            },
            info(message, title = 'Информация', timeout = 4000) {
                return notificationsService.showNotification(message, 'mblue', title, timeout);
            }
        };

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
