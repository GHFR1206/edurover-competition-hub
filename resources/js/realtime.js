import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/*
 * RoboRace realtime bootstrap (Laravel Reverb via the Pusher protocol).
 *
 * The layout meta tags decide whether this page wants live updates and on
 * which channels. Pages without the meta tag get no websocket connection.
 */

window.Pusher = Pusher;

export function initRealtime() {
    const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content;

    if (!meta('reverb-key')) {
        return null;
    }

    const echo = new Echo({
        broadcaster: 'reverb',
        key: meta('reverb-key'),
        wsHost: meta('reverb-host') || window.location.hostname,
        wsPort: Number(meta('reverb-port') || 8090),
        wssPort: Number(meta('reverb-port') || 8090),
        forceTLS: (meta('reverb-scheme') || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo = echo;

    return echo;
}

/**
 * Subscribe to the public event board and/or a category board, and reload the
 * page when a relevant update arrives. Reload is deliberate: it keeps the
 * server-rendered standings as the single source of truth (no drift).
 */
export function watchStandings() {
    const echo = window.Echo;
    const categoryId = document.querySelector('meta[name="live-category"]')?.content;
    const eventId = document.querySelector('meta[name="live-event"]')?.content;

    if (!echo) {
        return;
    }

    if (categoryId) {
        echo.channel(`category.${categoryId}`).listen('.standings.updated', () => window.location.reload());
    }

    if (eventId) {
        echo.channel(`event.${eventId}`).listen('.standings.updated', () => window.location.reload());
        echo.channel(`event.${eventId}`).listen('.run.status', () => window.location.reload());
    }
}

/**
 * Subscribe to a private arena channel (staff only) for run-status changes.
 */
export function watchArena() {
    const echo = window.Echo;
    const arenaId = document.querySelector('meta[name="live-arena"]')?.content;

    if (!echo || !arenaId) {
        return;
    }

    echo.private(`arena.${arenaId}`).listen('.run.status', () => window.location.reload());
}

document.addEventListener('DOMContentLoaded', () => {
    initRealtime();
    watchStandings();
    watchArena();
});
