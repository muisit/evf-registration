import { fetchJson, FetchResponse } from '../interface';
import { Registration } from '../schemas/registration';

export const saveregistration = function(eventId: number, fencerId:number, sideEventId: number, roleId: number) {
    return new Promise<Registration|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            fencer: fencerId,
            sideEvent: sideEventId,
            role: roleId
        };
        return fetchJson('POST', '/registrations', data)
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject("No response data");
                }
                resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
                return reject(err);
        });
    });
}
