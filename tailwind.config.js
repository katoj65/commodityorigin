import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/html/**/*.html',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                bg: '#0E0B07',
                surface: '#141009',
                surface2: '#1A150D',
                surface3: '#221B11',
                gold: '#C8862A',
                gold2: '#E09B3A',
                cream: '#F2EDE4',
                green: '#40916C',
                'green-dark': '#2D6A4F',
                red: '#E07070',
                up: '#52B788',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
