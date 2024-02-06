import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { CodeUser } from '../schemas/codes';

export const saveuser = function(user:CodeUser) {
    return new Promise<CodeUser|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: user.id,
            type: user.type,
            fencerId: user.fencerId
        };
        return fetchJson('POST', '/codes/users', { user: data })
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
