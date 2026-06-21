import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                ink: {
                    950: '#070B13',
                    900: '#0A0F1A',
                    850: '#0E1421',
                    800: '#121A28',
                    700: '#1A2435',
                    600: '#233044',
                    500: '#334259',
                    400: '#51607A',
                    300: '#7C8AA0',
                    200: '#AEB7C7',
                    100: '#E6EAF1',
                },
                gold: {
                    50: '#FBF6EA',
                    100: '#F5EACF',
                    200: '#EAD49B',
                    300: '#DFC074',
                    400: '#D4B062',
                    500: '#C39A45',
                    600: '#A07A33',
                },
                emerald2: '#34D399',
                amber2: '#FBBF24',
                crimson: '#F87171',
                crimsonDeep: '#9B1C1C',
            },
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', 'serif'],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                'ledger': '0 1px 0 0 rgba(212,176,98,0.06), 0 12px 30px -12px rgba(0,0,0,0.6)',
                'glow-gold': '0 0 0 1px rgba(212,176,98,0.25), 0 0 24px -6px rgba(212,176,98,0.35)',
            },
            backgroundImage: {
                'ledger-grid': "linear-gradient(rgba(212,176,98,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(212,176,98,0.04) 1px, transparent 1px)",
                'gold-sheen': "linear-gradient(135deg, #EAD49B 0%, #D4B062 45%, #A07A33 100%)",
            },
            backgroundSize: {
                'grid-sm': '44px 44px',
            },
            keyframes: {
                'pulse-ring': {
                    '0%': { boxShadow: '0 0 0 0 rgba(212,176,98,0.5)' },
                    '70%': { boxShadow: '0 0 0 8px rgba(212,176,98,0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(212,176,98,0)' },
                },
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'draw': {
                    '0%': { strokeDashoffset: '1000' },
                    '100%': { strokeDashoffset: '0' },
                },
            },
            animation: {
                'pulse-ring': 'pulse-ring 2s infinite',
                'fade-up': 'fade-up 0.5s ease-out both',
            },
        },
    },

    plugins: [forms],
};
