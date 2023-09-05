import { fetchJson } from './interface';
import { BasicDataSchema } from './schemas/basicdata';

export const basicData = function() {
    return new Promise<BasicDataSchema>((resolve, reject) => {       
        return fetchJson('GET', '/basic')
            .then( (data) => {
                if(!data) {
                    return reject("No response data");
                }

                return resolve(data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
                return reject(err);
        });
    });
}
