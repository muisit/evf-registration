import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const handout = function(code:string) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        return fetchJson('POST', '/accreditations/handout', { badge: code })
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
