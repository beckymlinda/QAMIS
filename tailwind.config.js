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
                heqamis: {
                    blue: '#0f2744',
                    'blue-light': '#1a3a5c',
                    green: '#8cc63f',
                    'green-dark': '#7ab833',
                },
            },
        },
    },

    plugins: [forms],
};
