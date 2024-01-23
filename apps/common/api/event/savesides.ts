import { fetchJson } from '../interface';
import type { FetchResponse } from '../interface';
import type { ReturnStatusSchema } from '../schemas/returnstatus';
import type { SideEvent } from '../schemas/sideevent';

export const savesides = function(sideEvents:SideEvent[]) {
    return new Promise<ReturnStatusSchema|null>((resolve, reject) => {     
        // restrict the data we are sending over
        var data:any[] = [];
        sideEvents.map((s:SideEvent) => {
            data.push({id: s.id, title: s.title, description: s.description, costs: s.costs, starts: s.starts});
        });
        return fetchJson('POST', '/events/sides', { sides: data })
            .then( (data:FetchResponse) => {
                if(!data || data.status != 200) {
                    return reject(data);
                }
                resolve(data.data);
        }, (err) => {
            reject(err);
        }).catch((err) => {
            return reject(err);
        });
    });
}
