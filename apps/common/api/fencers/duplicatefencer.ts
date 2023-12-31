import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Fencer } from '../schemas/fencer';

export const duplicatefencer = function(fencer:Fencer) {
    return new Promise<Fencer|null>((resolve, reject) => {       
        return fetchJson('POST', '/fencers/duplicate', { fencer: fencer })
            .then( (data:FetchResponse) => {
                if(!data || ![200, 409].includes(data.status)) {
                    return reject("No response data");
                }

                if (data.status == 200) {
                    return resolve(null);
                }
                else {
                    return resolve(data.data);
                }
        }, (err) => {
            reject(err);
        }).catch((err) => {
                return reject(err);
        });
    });
}
