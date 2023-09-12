import { fetchJson, FetchResponse } from '../interface';
import { Registrations } from '../schemas/registrations';

export const registrations = function(eventId: number, countryId: number) {
    return new Promise<Registrations>((resolve, reject) => {       
        return fetchJson('GET', '/events/' + eventId + '/registrations?country=' + countryId)
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
