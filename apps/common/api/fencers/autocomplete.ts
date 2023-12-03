import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { FencerList } from '../schemas/fencer';

export const autocomplete = function(searchdata:string) {
    return new Promise<FencerList>((resolve, reject) => {       
        return fetchJson('GET', '/fencers/autocomplete', searchdata)
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
