import type { Fencer } from "../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../common/api/schemas/registration";
import { is_valid } from "../../../../common/functions";

export function fencerIsHod(fencer:Fencer, countryId:number)
{
    let retval = false;
    if (fencer && fencer.registrations) {
        fencer.registrations.map((r:Registration) => {
            if (!is_valid(r.sideEventId) && is_valid(r.roleId)) {
                // the Head-of-Delegation role is fixed to ID 2
                if (r.roleId == 2) {
                    // check that this person is in fact HoD of the country of the fencer
                    retval = countryId == r.countryId;
                }
            }
        });
    }
    return retval;
}
