import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

import filamentPreset from './vendor/filament/support/tailwind.config.preset';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [filamentPreset],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/**/*.blade.php',
    ],

    safelist : [
        'sm:max-w-md',
        'max-w-2xl',
        'md:max-w-xl',
        'lg:max-w-3xl',
        'xl:max-w-4xl',
        'xl:max-w-5xl',
        '2xl:max-w-7xl',
    ],

    theme: {
        extend: {
            
        },
    },

    plugins: [forms, typography],
};
