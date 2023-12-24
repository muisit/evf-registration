import { useAuthStore } from '../../stores/auth';

export function download(id:number)
{
    const auth = useAuthStore();
    window.location = import.meta.env.VITE_API_URL + '/accreditations/summary/' + id + '?event=' + (auth && auth.eventId ? auth.eventId : 0);
}
