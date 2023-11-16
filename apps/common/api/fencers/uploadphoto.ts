import { uploadFile, FetchResponse } from '../interface';
import { Fencer } from '../schemas/fencer';
import { ReturnStatusSchema } from '../schemas/returnstatus';

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
