import { duplicatefencer } from "../../../../../common/api/fencers/duplicatefencer";
import type { Fencer } from "../../../../../common/api/schemas/fencer";

export function duplicateFencerCheck(fencer:Fencer)
{
    return duplicatefencer({
        id: fencer.id,
        lastName: fencer.lastName,
        firstName: fencer.firstName,
        dateOfBirth: fencer.dateOfBirth,
        countryId: fencer.countryId
    });
}