import type { Fencer } from "../../../../common/api/schemas/fencer";

export function mergeFencer(fencer1: Fencer, fencer2: Fencer)
{
    fencer1.firstName = fencer2.firstName;
    fencer1.lastName = fencer2.lastName;
    fencer1.countryId = fencer2.countryId;
    fencer1.gender = fencer2.gender;
    fencer1.dateOfBirth = fencer2.dateOfBirth;
    fencer1.photoStatus = fencer2.photoStatus;
    // do not merge registrations, this method is only used to adjust fencer core data
    return fencer1;
}