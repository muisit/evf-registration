import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Event } from '../schemas/event';
import { useAuthStore } from '../../stores/auth';

export const saveeventconfig = function(config:any) {
    return new Promise<Event|null>((resolve, reject) => {     
        // restrict the data we are sending over
        const auth = useAuthStore();
        var data:any = {
            id: auth.eventId,
            config: JSON.stringify(config)
        };

        return fetchJson('POST', '/events/config', { event: data })
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject(data);
                }
                resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
