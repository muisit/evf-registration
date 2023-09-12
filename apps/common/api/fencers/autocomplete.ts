import { fetchJson, FetchResponse } from '../interface';
import { FencerList } from '../schemas/fencer';

export const autocomplete = function(searchdata) {
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
