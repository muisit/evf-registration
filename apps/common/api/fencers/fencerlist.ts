import { getId } from '../../functions';
import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { CountrySchema } from '../schemas/country';
import type { FencerList } from '../schemas/fencer';

export const fencerlist = function(country:CountrySchema|number) {
    return new Promise<FencerList>((resolve, reject) => {       
        return fetchJson('GET', '/fencers', { country: getId(country)})
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
