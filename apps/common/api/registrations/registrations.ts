import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { Registrations } from '../schemas/registrations';

export const registrations = function() {
    return new Promise<Registrations>((resolve, reject) => {       
        return fetchJson('GET', '/registrations')
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

export const allRegistrations = function() {
    return new Promise<Registrations>((resolve, reject) => {       
        return fetchJson('GET', '/registrations', {all:1})
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
