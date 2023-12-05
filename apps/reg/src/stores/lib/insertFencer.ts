import { mergeFencer } from "./mergeFencer";
import { decorateFencer } from "./decorateFencer";
import { useDataStore } from "../data";
import type { Fencer } from "../../../../common/api/schemas/fencer";

export function insertFencer(fencer:Fencer)
{
    const dataStore = useDataStore();
    var allFencers = Object.assign({}, dataStore.fencerData);
    var fid = 'f' + fencer.id;
    if (allFencers[fid]) {
        fencer = mergeFencer(allFencers[fid], fencer);
    }
    
    allFencers[fid] = decorateFencer(fencer);
    dataStore.fencerData = allFencers;
}