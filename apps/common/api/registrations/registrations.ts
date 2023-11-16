import { fetchJson, FetchResponse } from '../interface';
import { Registrations } from '../schemas/registrations';

export const registrations = function() {
    return new Promise<Registrations>((resolve, reject) => {       
        return fetchJson('GET', '/registrations')
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
