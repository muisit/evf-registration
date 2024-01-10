import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { OverviewLine } from '../schemas/overviewline';

export const overview = function(): Promise<OverviewLine[]> {
    return new Promise<Array<OverviewLine>>((resolve, reject) => {       
        return fetchJson('GET', '/events/overview')
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
