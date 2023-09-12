import { fetchJson, FetchResponse } from '../interface';
import { ReturnStatusSchema } from '../schemas/returnstatus';

export const logout = function() {
    return new Promise<ReturnStatusSchema>((resolve, reject) =>  {
        return fetchJson('GET', '/auth/logout')
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject("No response data");
                }
                return resolve({ status: data.data.status, message: data.data.message || ''});
            }, (err) => {
                reject(err);
            })
            .catch((e) => {
                return reject(e);
            });
    });
}
