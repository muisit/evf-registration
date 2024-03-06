import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { AccreditationDocument } from '../schemas/accreditationdocument';

export const documents = function() {
    return new Promise<AccreditationDocument[]>((resolve, reject) => {       
        return fetchJson('GET', '/accreditations/documents')
            .then( (data:FetchResponse) => {
                if(!data || (data.status != 200 && data.status != 403)) {
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
