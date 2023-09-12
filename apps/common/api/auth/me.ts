import { fetchJson, FetchResponse } from '../interface';
import { MeSchema } from '../schemas/me';

export const me = function(retries:number = 0) {
    return new Promise<MeSchema>((resolve, reject) => {       
        return fetchJson('GET', '/auth/me')
            .then( (data:FetchResponse) => {                
                if(!data || data.status != 200) {
                    return reject("No response data");
                }

                console.log('resolving me using token ', data, data.data.token);
                return resolve({
                    token: data.data.token,
                    username: data.data.username,
                    status: data.data.status,
                    credentials: data.data.credentials,
                    countryId: data.data.countryId
                });
        }, (err) => {
            reject(err);
        }).catch(() => {
            if (retries >= 0 && retries < 3) {
                return resolve(new Promise<MeSchema>((resolve2) => setTimeout(() => { return resolve2(me(retries+1)); },200)));
            }
            else {
                if (retries >= 3) console.log('giving up after 3 retries');
                else console.log('giving up single Me try');
                return reject("giving up after retries");
            }
        });
    });
}
