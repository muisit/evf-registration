import { fetchJson } from './interface';
import { MeSchema } from './schemas/me';

export const me = function(retries:number = 0) {
    console.log('calling ME');
    return new Promise<MeSchema>((resolve, reject) => {       
        return fetchJson('GET', '/auth/me')
            .then( (data) => {                
                console.log("ME return data is ",data);
                if(!data) {
                    return reject("No response data");
                }

                return resolve({ token: data.token, username: data.username, status: data.status, credentials: data.credentials});
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
