import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Fencer } from '../schemas/fencer';

export const savefencer = function(fencer:Fencer) {
    return new Promise<Fencer|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: fencer.id,
            firstName: fencer.firstName,
            lastName: fencer.lastName,
            dateOfBirth: fencer.dateOfBirth,
            gender: fencer.gender,
            photoStatus: fencer.photoStatus,
            countryId: fencer.countryId
        };
        return fetchJson('POST', '/fencers', { fencer: data })
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
