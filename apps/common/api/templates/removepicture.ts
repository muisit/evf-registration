import { fetchJson } from '../interface';
import type { PictureContent, TemplateSchema } from '../schemas/template';
import type { ReturnStatusSchema } from '../schemas/returnstatus';
import type { FetchResponse } from '../interface';

export const removepicture = function(template:TemplateSchema, picture:PictureContent) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {       
        return fetchJson('POST', '/templates/' + template.id + '/picture/' + picture.file_id + '/remove')
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
