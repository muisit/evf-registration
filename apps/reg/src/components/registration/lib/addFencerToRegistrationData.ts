import { Fencer } from "../../../../../common/api/schemas/fencer";
import { useDataStore } from "../../../stores/data";

export function addFencerToRegistrationData(fencer:Fencer)
{
    const data = useDataStore();
    var fencerData = Object.assign({}, data.fencerData);
    fencerData['f' + fencer.id] = fencer;
    data.fencerData = fencerData;
}