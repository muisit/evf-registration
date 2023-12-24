import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const summary = function(type:string, typeId:number) {
    return new Promise<ReturnStatusSchema>((resolve, reject) => {       
        return fetchJson('POST', '/accreditations/summary', {summary: {type: type, typeId: typeId}})
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject("No response data");
                }

                return resolve({ status: data.data.status, message: data.data.message || ''});
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
