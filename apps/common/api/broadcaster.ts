import Echo from 'laravel-echo';
import PollcastConnector from '../lib/pollcast/pollcast'
import { useAuthStore } from '../stores/auth';

export const broadcaster = () => {
    console.log('creating new pollcastConnector for Echo', import.meta.env.VITE_API_URL + "/pollcast/connect");
    const auth = useAuthStore();
    let retval = new Echo({
        broadcaster: PollcastConnector,
        routes: {
            connect: import.meta.env.VITE_API_URL + "/pollcast/connect",
            receive: import.meta.env.VITE_API_URL + "/pollcast/subscribe/messages",
            publish: import.meta.env.VITE_API_URL + "/pollcast/publish",
            subscribe: import.meta.env.VITE_API_URL + "/pollcast/channel/subscribe",
            unsubscribe: import.meta.env.VITE_API_URL + "/pollcast/channel/unsubscribe",
        },
        polling: 10000,
        withCredentials: true
    });

    return retval;
};
