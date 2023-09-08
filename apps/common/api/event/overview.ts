import { fetchJson } from '../interface';
import { OverviewLine } from '../schemas/overviewline';

export const overview = function(eventId: number) {
    return new Promise<Array<OverviewLine>>((resolve, reject) => {       
        return fetchJson('GET', '/events/' + eventId + '/overview')
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
