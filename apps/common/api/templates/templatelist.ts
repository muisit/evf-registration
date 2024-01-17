import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { TemplateSchema } from '../schemas/template';

export const templatelist = function() {
    return new Promise<Array<TemplateSchema>>((resolve, reject) => {       
        return fetchJson('GET', '/templates')
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
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
