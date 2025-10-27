/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const pusherAppKey = import.meta.env.VITE_PUSHER_APP_KEY; // Maps the old MIX_PUSHER_APP_KEY to Vite's VITE_PUSHER_APP_KEY.
const pusherAppCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1'; // Mirrors MIX_PUSHER_APP_CLUSTER through Vite with a sensible default.
const pusherHost = import.meta.env.VITE_PUSHER_HOST ?? `ws-${pusherAppCluster}.pusher.com`; // Keeps optional PUSHER_HOST overrides wired to Vite.
const pusherScheme = import.meta.env.VITE_PUSHER_SCHEME ?? 'https'; // Aligns PUSHER_SCHEME configuration with Vite-driven builds.
const pusherPort = import.meta.env.VITE_PUSHER_PORT; // Shares PUSHER_PORT across ws/wss just like the Laravel preset expects.
const pusherWsPort = Number(pusherPort ?? 80); // Defaults to 80 when no explicit PUSHER_PORT is defined for ws connections.
const pusherWssPort = Number(pusherPort ?? 443); // Defaults to 443 when no explicit PUSHER_PORT is defined for secure ws connections.

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: pusherAppKey, // Echo now reads the key from import.meta.env instead of process.env.
    cluster: pusherAppCluster, // Cluster value also flows through import.meta.env for Vite compatibility.
    wsHost: pusherHost, // Host keeps parity with the VITE_PUSHER_HOST override or defaults to the public cluster endpoint.
    wsPort: pusherWsPort, // WebSocket connections respect any custom port exposed via Vite.
    wssPort: pusherWssPort, // Secure WebSocket connections share the same port configuration for consistency.
    forceTLS: pusherScheme === 'https', // TLS usage follows the PUSHER_SCHEME environment flag exposed to Vite.
    enabledTransports: ['ws', 'wss'] // Allow both secure and insecure WebSocket transports just like Laravel's Vite preset.
});
