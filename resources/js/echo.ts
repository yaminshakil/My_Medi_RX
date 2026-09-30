import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

console.log('Pusher key:', import.meta.env.VITE_PUSHER_APP_KEY);
console.log('Pusher cluster:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo;
    }
}

window.Pusher = Pusher;

Pusher.logToConsole = true;

const echo = new Echo({
    broadcaster: 'pusher',

    key: import.meta.env.VITE_PUSHER_APP_KEY,

    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,

    forceTLS: true,

    enabledTransports: ['ws', 'wss'],
});

export default echo;
