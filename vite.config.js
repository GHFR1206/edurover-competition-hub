import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // RoboRace role stylesheets (ported from the frontend team).
                // Each role layout loads exactly one so class names never collide.
                'resources/css/roborace/public.css',
                'resources/css/roborace/auth.css',
                'resources/css/roborace/coach.css',
                'resources/css/roborace/event-admin.css',
                'resources/css/roborace/juri.css',
                'resources/css/roborace/operator.css',
                'resources/css/roborace/chief.css',
                'resources/css/roborace/super-admin.css',
                // Realtime (Reverb via Pusher protocol) — shared entry.
                'resources/js/realtime.js',
            ],
            refresh: true,
        }),
    ],
});
