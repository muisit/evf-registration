import { uploadFile } from '../interface';
import type { FetchResponse } from '../interface';
import type { Fencer } from '../schemas/fencer';
import type { ReturnStatusSchema } from '../schemas/returnstatus';

export const uploadphoto = function(fencer:Fencer, fileObject:any) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {       
        return uploadFile('/fencers/' + fencer.id + '/photo', fileObject, {})
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
