import { fetchJson, FetchResponse } from '../interface';
import { Registration } from '../schemas/registration';
import { ReturnStatusSchema } from '../schemas/returnstatus';

export const payregistration = function(registrations:Registration[], paidHod:boolean|null, paidOrg:boolean|null) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data:any = {
            registrations: registrations.map((reg:Registration) => reg.id)
        };
        if (paidHod !== null) {
            data.paidHod = paidHod ? 'Y' : 'N';
        }
        if (paidOrg !== null) {
            data.paidOrg = paidOrg ? 'Y' : 'N';
        }

        return fetchJson('POST', '/registrations/pay', { payment: data })
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
