import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { AccreditationDocument } from '../schemas/accreditationdocument';

export const savedocument = function(doc:AccreditationDocument) {
    return new Promise<AccreditationDocument|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data:any = {id: doc.id || 0};
        if (doc.badge) data.badge = doc.badge;
        if (doc.fencerId) data.fencerId = doc.fencerId;
        if (doc.card) data.card = doc.card;
        if (doc.document) data.document = doc.document;
        if (doc.payload) data.payload = JSON.stringify(doc.payload);
        if (doc.status) data.status = doc.status;

        return fetchJson('POST', '/accreditations/document', { doc: data })
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
