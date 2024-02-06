import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const generatecodes = function() {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {       
        return fetchJson('GET', '/events/generate')
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
