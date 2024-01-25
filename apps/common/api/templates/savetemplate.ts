import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { TemplateSchema } from '../schemas/template';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const savetemplate = function(template:TemplateSchema) {
    return new Promise<TemplateSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: template.id,
            name: template.name,
            content: JSON.stringify(template.content),
            isDefault: template.isDefault
        };
        return fetchJson('POST', '/templates', { template: data })
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

export const copytemplate = function(template:TemplateSchema) {
    return new Promise<TemplateSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data = {
            id: template.id,
            name: template.name,
            content: JSON.stringify(template.content),
            isDefault: template.isDefault,
            copy: true
        };
        return fetchJson('POST', '/templates', { template: data })
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
