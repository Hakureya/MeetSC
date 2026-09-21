import defaultTheme from 'tailwindcss/defaultTheme';

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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#EAF3FE',
                    100: '#D6E9FD',
                    500: '#1E6FEB',
                    600: '#0F5FE0',
                    700: '#0C4CB4',
                },
                pln: {
                    yellow: '#FFE000',
                    red: '#E4032E',
                },
                busy: {
                    bg: '#FDE4E4',
                    border: '#F6B8B8',
                    text: '#C22323',
                },
                free: {
                    bg: '#DFFAE6',
                    border: '#A9E8BB',
                    text: '#1E8A44',
                },
            },
            borderRadius: {
                xl: '0.875rem',
            },
        },
    },
    plugins: [],
};
