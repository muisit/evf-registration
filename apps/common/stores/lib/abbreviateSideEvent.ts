import type { SideEvent } from "../../api/schemas/sideevent";
import { is_valid } from "../../functions";
import { useDataStore } from "../../../reg/src/stores/data";

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
            abbr += word[0].toLocaleUpperCase();
        }
    }
    return abbr;    
}