import { FencerList } from "../../../../../common/api/schemas/fencer";

export function filterSuggestionsFromFencerList(name:string, fencers:FencerList): FencerList
{
    var lowername = name.toLocaleLowerCase();
    return fencers.map((fencer) => {
            if (   fencer.lastName
                && fencer.lastName.length >= lowername.length
                && fencer.lastName.substring(0, lowername.length).toLowerCase() == lowername) {
                return fencer;
            }
            else {
                return null;
            }
        }).filter((fencer) => fencer != null);
}