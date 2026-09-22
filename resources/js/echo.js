import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb berbicara lewat protokol yang kompatibel dengan Pusher, jadi
// laravel-echo tetap memakai pusher-js sebagai klien WebSocket di bawahnya.
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Halaman lain (mis. resources/views/schedule/index.blade.php) memuat script-nya
// sendiri lewat tag <script> biasa, yang jalan SEBELUM app.js (modul Vite di-defer).
// Event ini memberi tahu mereka kapan window.Echo sudah siap dipakai.
window.dispatchEvent(new Event('echo:ready'));
