import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Code } from '../schemas/codes';
import type { CodeProcessStatus } from '../schemas/codeprocessstatus';

export const checkcode = function(code:Code, action:string) {
    return new Promise<CodeProcessStatus>((resolve, reject) => {       
        return fetchJson('POST', '/codes', {codes: [code.original], action: action})
            .then( (data:FetchResponse) => {
                if(!data || (data.status != 200 && data.status != 403)) {
                    return reject("No response data");
                }

                return resolve(data.data as CodeProcessStatus);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}

export const checkcodes = function(codes:Code[], action:string) {
    return new Promise<CodeProcessStatus>((resolve, reject) => {  
        let data:string[] = [];
        codes.map((cd) => data.push(cd.original));     
        return fetchJson('POST', '/codes', {codes: data, action: action})
            .then( (data:FetchResponse) => {
                if(!data || (data.status != 200 && data.status != 403)) {
                    return reject("No response data");
                }

                return resolve(data.data as CodeProcessStatus);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
