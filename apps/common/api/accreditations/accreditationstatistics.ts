import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { AccreditationStatistics } from '../schemas/accreditationstatistics';

export const accreditationstatistics = function(): Promise<AccreditationStatistics[]> {
    return new Promise<Array<AccreditationStatistics>>((resolve, reject) => {       
        return fetchJson('GET', '/accreditations/statistics')
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
