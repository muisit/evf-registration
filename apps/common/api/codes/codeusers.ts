import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { CodeUser } from '../schemas/codes';

export const codeusers = function() {
    return new Promise<CodeUser[]>((resolve, reject) => {       
        return fetchJson('GET', '/codes/users')
            .then( (data:FetchResponse) => {
                if(!data || (data.status != 200 && data.status != 403)) {
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
