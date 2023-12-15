import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const login = function(username:string, password:string) {
    return new Promise<ReturnStatusSchema>((resolve, reject) =>  {
        return fetchJson('POST', '/auth/login', {username: username, password: password})
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject("No response data");
                }
                return resolve({ status: data.data.status, message: data.data.message || ''});
            }, (err) => {
                reject(err);
            })
            .catch(() => {
                return reject("giving up after retries");
            });
    });
}
