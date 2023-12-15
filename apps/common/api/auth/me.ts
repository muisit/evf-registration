import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { MeSchema } from '../schemas/me';

export const me = function(retries:number = 0): Promise<MeSchema> {
    return new Promise<MeSchema>((resolve, reject) => { 
        console.log('calling fetch');      
        return fetchJson('GET', '/auth/me')
            .then( (data:FetchResponse) => {                
                if(!data || data.status != 200) {
                    throw new Error("No response data");
                }

                return resolve({
                    token: data.data.token,
                    username: data.data.username,
                    status: data.data.status,
                    credentials: data.data.credentials,
                    countryId: data.data.countryId
                } as MeSchema);
        }, (err) => {
            console.log('catching err', err);
            reject(err);
        }).catch(() => {
            console.log('catch me');
            if (retries >= 0 && retries < 3) {
                return resolve(new Promise<MeSchema>((resolve2) => setTimeout(() => { return resolve2(me(retries+1)); },400)));
            }
            else {
                if (retries >= 3) console.log('giving up after 3 retries');
                else console.log('giving up single Me try');
                if (confirm("There seems to be a server error that prevents the application from starting correctly. Please reload the page and try again")) {
                    return resolve(new Promise<MeSchema>((resolve2) => setTimeout(() => { return resolve2(me(0)); },400)));
                }
            }
        });
    });
}
