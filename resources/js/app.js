import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Import the component
import NavLink from '@/Components/NavLink.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { Head } from '@inertiajs/vue3';
import ToastService from 'primevue/toastservice';
import store from './store/index';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
PrimeVue.theme = 'light';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ToastService)
            .component('AuthenticatedLayout', AuthenticatedLayout)
            .component('Head', Head)
            .component('NavLink', NavLink)
            .component('InputLabel', InputLabel)
            .component('PrimaryButton', PrimaryButton)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: 'off',
                    }
                }
            })
            .use(store)
            .mount(el);
            
    },
    progress: {
        color: '#4B5563',
    },
});
