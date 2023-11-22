import { fetchJson, FetchResponse } from '../interface';
import { AccreditationList } from '../schemas/accreditation';

export const accreditations = function(id:number) {
    return new Promise<AccreditationList>((resolve, reject) => {       
        return fetchJson('GET', '/fencers/' + id + '/accreditations')
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
