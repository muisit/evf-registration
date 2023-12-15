import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const regenerate = function() {
    return new Promise<ReturnStatusSchema>((resolve, reject) => {       
        return fetchJson('GET', '/accreditations/regenerate')
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject("No response data");
                }

                return resolve({ status: data.data.status, message: data.data.message || ''});
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
