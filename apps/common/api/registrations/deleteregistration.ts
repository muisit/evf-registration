import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const deleteregistration = function(id:number) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: id,
        };
        return fetchJson('POST', '/registrations/delete', { registration: data })
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
