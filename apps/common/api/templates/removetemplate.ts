import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { TemplateSchema } from '../schemas/template';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const removetemplate = function(template:TemplateSchema) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: template.id,
        };
        return fetchJson('POST', '/templates/remove', { template: data })
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
