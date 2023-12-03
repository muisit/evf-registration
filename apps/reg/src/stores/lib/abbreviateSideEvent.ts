import type { SideEvent } from "../../../../common/api/schemas/sideevent";
import { is_valid } from "../../../../common/functions";
import { useDataStore } from "../data";

export function abbreviateSideEvent(se:SideEvent)
{
    const dataStore = useDataStore();
    var abbr='??';
    if(is_valid(se.competition) && se.competition) {
        var wpn = se.competition.weapon ? se.competition.weapon : {abbr:'?'};
        var cat = se.competition.category ? se.competition.category : {abbr: '?'};
        abbr = '' + wpn.abbr + cat.abbr;
    }
    else {
        var words=se.title.split(' ');
        abbr = "";
        for(var i in words) {
            var word = words[i];
            abbr += word[0];
        }
    }
    return abbr;    
}