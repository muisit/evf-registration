import type { FencerList } from "../../../../../common/api/schemas/fencer";

export function filterSuggestionsFromFencerList(name:string, fencers:FencerList): FencerList
{
    var lowername = name.toLocaleLowerCase();
    let retval = fencers.map((fencer) => {
            if (   fencer.lastName
                && fencer.lastName.length >= lowername.length
                && fencer.lastName.substring(0, lowername.length).toLowerCase() == lowername) {
                return fencer;
            }
            else {
                return null;
            }
        }).filter((fencer) => fencer != null);
    return retval as FencerList; // we know we filtered out all null fencers
}