import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            boxShadow: {
                sm: '0 0 2px 0 rgba(0, 0, 0, 0.05), 0 0 4px 0 rgba(0, 0, 0, 0.05)',
                DEFAULT: '0 0 6px 0 rgba(0, 0, 0, 0.1), 0 0 10px 0 rgba(0, 0, 0, 0.1)',
                md: '0 0 10px 0 rgba(0, 0, 0, 0.1), 0 0 15px 0 rgba(0, 0, 0, 0.1)',
                lg: '0 0 15px 0 rgba(0, 0, 0, 0.1), 0 0 20px 0 rgba(0, 0, 0, 0.1)',
                xl: '0 0 25px 5px rgba(0, 0, 0, 0.1), 0 0 30px 10px rgba(0, 0, 0, 0.1)',
                '2xl': '0 0 40px 10px rgba(0, 0, 0, 0.25)',
                inner: 'inset 0 0 4px 0 rgba(0, 0, 0, 0.06)',
                none: 'none',
            },
            colors: {
                primary: '#A7704A', // pink-600
                secondary: '#649182', // or any other color you want
            },

            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
