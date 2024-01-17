import { uploadFile } from '../interface';
import type { FetchResponse } from '../interface';
import type { PictureContent, TemplateSchema } from '../schemas/template';

export const uploadpicture = function(template:TemplateSchema, fileObject:any) {
    return new Promise<PictureContent|null>((resolve, reject) => {       
        return uploadFile('/templates/' + template.id + '/picture', fileObject, {})
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
