import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Event } from '../schemas/event';

export const getEvent = function(id:number) {
    return new Promise<Event>((resolve, reject) => {       
        return fetchJson('GET', '/events/' + id)
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject("No response data");
                }

                return resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
                return reject(err);
        });
    });
}
