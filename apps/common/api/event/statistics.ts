import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { EventStatistics } from '../schemas/eventstatistics';

export const statistics = function(): Promise<EventStatistics> {
    return new Promise<EventStatistics>((resolve, reject) => {       
        return fetchJson('GET', '/events/statistics')
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
