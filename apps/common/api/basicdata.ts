import { fetchJson } from './interface';
import type { FetchResponse } from './interface';
import type { BasicDataSchema } from './schemas/basicdata';

export const basicData = function(restrict?:string) {
    return new Promise<BasicDataSchema>((resolve, reject) => {       
        return fetchJson('GET', '/basic' + (restrict ? '?restrict=' + restrict : ''))
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
