import { Fencer } from "../../../../../common/api/schemas/fencer";
import { SideEvent } from "../../../../../common/api/schemas/sideevent";
import { my_category_is_older } from "../../../../../common/functions";

export function filterEventCategoryYounger (fencer:Fencer, sideevent:SideEvent):boolean
{
    if( sideevent.competition
        && sideevent.competition.category
        && sideevent.competition.weapon
        && sideevent.competition.category.type != 'T'
        && my_category_is_older(fencer.categoryNum || 0, sideevent.competition.category.value || 0)
        && sideevent.competition.weapon.gender == fencer.gender) {
            return true;
    }
    return false;
}