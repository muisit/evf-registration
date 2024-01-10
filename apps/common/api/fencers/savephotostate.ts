import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Fencer } from '../schemas/fencer';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const savephotostate = function(fencer:Fencer) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: fencer.id,
            photoStatus: fencer.photoStatus,
        };
        return fetchJson('POST', '/fencers/' + fencer.id + '/photostate', { fencer: data })
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject(data);
                }
                return resolve(data.data);
        }, (err) => {
            return reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
