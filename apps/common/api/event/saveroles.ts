import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';
import type { EventRole } from '../schemas/eventroles';

export const saveroles = function(roles:EventRole[]) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        return fetchJson('POST', '/events/roles', { roles: roles })
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject(data);
                }data
                resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
