import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { EventRoles } from '../schemas/eventroles';

export const eventroles = function() {
    return new Promise<EventRoles|null>((resolve, reject) => {       
        return fetchJson('GET', '/events/roles')
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
