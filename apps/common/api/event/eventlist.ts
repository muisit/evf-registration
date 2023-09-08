import { fetchJson } from '../interface';
import { Event } from '../schemas/event';

export const eventlist = function() {
    return new Promise<Array<Event>>((resolve, reject) => {       
        return fetchJson('GET', '/events')
            .then( (data) => {
                if(!data) {
                    return reject("No response data");
                }

                return resolve(data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
                return reject(err);
        });
    });
}
