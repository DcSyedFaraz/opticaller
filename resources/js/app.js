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
import 'primeicons/primeicons.css';
import ConfirmationService from 'primevue/confirmationservice';
import { definePreset } from '@primevue/themes';




const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
PrimeVue.theme = 'light';

const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '#A7704A',
            100: '#A7704A',
            200: '#A7704A',
            300: '#A7704A',
            400: '#A7704A',
            500: '#A7704A',
            600: '#A7704A',
            700: '#A7704A',
            800: '#A7704A',
            900: '#A7704A',
            950: '#A7704A'
        }
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ToastService)
            .use(ConfirmationService)
            .component('AuthenticatedLayout', AuthenticatedLayout)
            .component('Head', Head)
            .component('NavLink', NavLink)
            .component('InputLabel', InputLabel)
            .component('PrimaryButton', PrimaryButton)
            .use(PrimeVue, {
                ripple: true,
                theme: {
                    preset: MyPreset,
                    options: {
                        darkModeSelector: 'off',
                    },
                }
            })
            .use(store)
            .mount(el);

    },
    progress: {
        color: '#4B5563',
    },
});
