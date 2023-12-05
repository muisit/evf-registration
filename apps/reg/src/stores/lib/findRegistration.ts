import type { Fencer, FencerById  } from "../../../../common/api/schemas/fencer";
import type { SideEvent } from "../../../../common/api/schemas/sideevent";
import type { Registration } from "../../../../common/api/schemas/registration";

export function findRegistration(dataList:FencerById, pFencer:Fencer, sideEvent:SideEvent|null, roleId:number|null):Registration|null
{
    let found:Registration|null = null;
    Object.keys(dataList).map((key:string) => {
        let fencer = dataList[key];
        if (fencer.id == pFencer.id) {
            fencer.registrations?.map((registration) => {
                // either the side-event matches, or the role matches
                // currently we do not support roles related to specific sideevents
                if (sideEvent && registration.sideEventId == sideEvent.id) {
                    found = registration;
                }
                else if (!sideEvent && registration.roleId == roleId) {
                    found = registration;
                }
            });
        }
    });
    return found;
}