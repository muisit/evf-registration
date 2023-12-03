import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Registration } from '../schemas/registration';

export const saveregistration = function(registration:Registration) {
    return new Promise<Registration|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            fencerId: registration.fencerId,
            sideEventId: registration.sideEventId,
            roleId: registration.roleId,
            payment: registration.payment,
            team: registration.team,
        };
        return fetchJson('POST', '/registrations', { registration: data })
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject(data);
                }
                resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
