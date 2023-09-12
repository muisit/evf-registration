import { fetchJson, FetchResponse } from './interface';
import { BasicDataSchema } from './schemas/basicdata';

export const basicData = function() {
    return new Promise<BasicDataSchema>((resolve, reject) => {       
        return fetchJson('GET', '/basic')
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
