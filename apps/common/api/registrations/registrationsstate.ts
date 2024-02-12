import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Registration } from '../schemas/registration';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const registrationsstate = function(registrations:Registration[], state:string, previous?:string) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data:any = {
            registrations: registrations.map((reg:Registration) => reg.id),
            value: state
        };
        if (previous) {
            data.previous = previous;
        }

        return fetchJson('POST', '/registrations/state', { state: data })
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
