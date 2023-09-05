import { fetchJson } from './interface';
import { ReturnStatusSchema } from './schemas/returnstatus';

export const logout = function() {
    return new Promise<ReturnStatusSchema>((resolve, reject) =>  {
        return fetchJson('GET', '/auth/logout')
            .then( (data) => {
                if(!data) {
                    return reject("No response data");
                }
                return resolve({ status: data.status, message: data.message || ''});
            }, (err) => {
                reject(err);
            })
            .catch((e) => {
                return reject(e);
            });
    });
}
