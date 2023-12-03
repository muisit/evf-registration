import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { SideEvent } from "../../../../../common/api/schemas/sideevent";

export function filterEventTeamVeterans (fencer:Fencer, sideevent:SideEvent):boolean
{
    if( sideevent.competition
        && sideevent.competition.category?.type == 'T' 
        && sideevent.competition.category?.abbr == 'T' 
        && sideevent.competition.weapon?.gender == fencer.gender
        && (fencer.categoryNum || 0) < 3
        && (fencer.categoryNum || 0) > 0) {
            return true;
    }
    return false;
}
