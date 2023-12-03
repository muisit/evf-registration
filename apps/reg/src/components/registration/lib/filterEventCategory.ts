import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { SideEvent } from "../../../../../common/api/schemas/sideevent";

export function filterEventCategory (fencer:Fencer, sideevent:SideEvent):boolean
{
    if( sideevent.competition
        && sideevent.competition.category?.type != 'T' 
        && sideevent.competition.category?.value == (fencer.categoryNum || 0)
        && sideevent.competition.weapon?.gender == fencer.gender) {
            return true;
    }
    return false;
}
