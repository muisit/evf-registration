import { Registrations } from "../../../../common/api/schemas/registrations";
import { mergeFencer } from "./mergeFencer";
import { decorateFencer } from "./decorateFencer";
import { useDataStore } from "../data";

export function registrationToFencers(registrationData:Registrations)
{
    const dataStore = useDataStore();
    console.log('converting registration to list of fencers', registrationData);
    // we receive a list of fencers and a list of registrations
    // first we normalise the fencer data and merge the entries with what we already
    // have in store
    var allFencers = Object.assign({}, dataStore.fencerData);
    if (registrationData.fencers) {
        registrationData.fencers.forEach((fencer) => {
            var fid = 'f' + fencer.id;
            if (allFencers[fid]) {
                fencer = mergeFencer(allFencers[fid], fencer);
            }
            decorateFencer(fencer);
            allFencers[fid] = fencer;
        });
    }

    // assign all registrations to the right fencer
    if (registrationData.registrations) {
        registrationData.registrations.forEach((reg) => {
            var fid = 'f' + reg.fencerId;
            if (allFencers[fid]) {
                allFencers[fid].registrations.push(reg);
            }
        })
    }
    dataStore.fencerData = allFencers; // make sure the state changes
}