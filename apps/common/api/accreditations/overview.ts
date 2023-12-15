import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { AccreditationOverviewLine } from '../schemas/accreditation';

export const overview = function(): Promise<AccreditationOverviewLine[]> {
    return new Promise<Array<AccreditationOverviewLine>>((resolve, reject) => {       
        return fetchJson('GET', '/accreditations/overview')
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
