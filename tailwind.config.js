import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brown: {
                    DEFAULT: '#5c4033',
                    100: '#b08d74',
                    200: '#a07968',
                    300: '#8f6a59',
                    400: '#7e5b4a',
                    500: '#5c4033',
                    600: '#53382d',
                    700: '#4e2e1c',
                    800: '#3e2416',
                    900: '#2e180f',
                },
            },
        },
    },

    plugins: [forms],
};
