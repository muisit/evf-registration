import { Fencer } from "../../../../../common/api/schemas/fencer";
import { SideEvent } from "../../../../../common/api/schemas/sideevent";

export function filterEventTeamGrandVeterans (fencer:Fencer, sideevent:SideEvent):boolean
{
    if( sideevent.competition
        && sideevent.competition.category
        && sideevent.competition.weapon
        && sideevent.competition.category.type == 'T' 
        && sideevent.competition.category.abbr == 'T(G)' 
        && sideevent.competition.weapon.gender == fencer.gender
        && (fencer.categoryNum || 0) > 2) {
            return true;
    }
    return false;
}
