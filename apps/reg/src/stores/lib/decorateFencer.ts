import { Fencer } from "../../../../common/api/schemas/fencer";
import { is_valid, date_to_category, date_to_category_num, parse_date } from "../../../../common/functions";
import { useDataStore } from "../data";

export function decorateFencer(fencer:Fencer)
{
    const dataStore = useDataStore();
    if (is_valid(fencer.countryId) && dataStore.countriesById['c' + fencer.countryId]) {
        fencer.country = dataStore.countriesById['c' + fencer.countryId];
    }
    else {
        fencer.country = null;
        fencer.countryId = 0;
    }

    fencer.lastName = fencer.lastName.toUpperCase();
    fencer.fullName = fencer.lastName + ", " + fencer.firstName;

    var now = parse_date(null);
    fencer.birthYear = now.year().toString();
    if (fencer.dateOfBirth) {
        fencer.birthYear = parse_date(fencer.dateOfBirth).year().toString();
    }
    // this blocks infants from being registered, but hey...
    if (fencer.birthYear == now.year().toString()) {
        fencer.birthYear = 'unknown';
        fencer.dateOfBirth = null;
        fencer.category = 'None';
        fencer.categoryNum = 0;
    }
    else {
        fencer.category = date_to_category(fencer.dateOfBirth, dataStore.currentEvent.opens);
        fencer.categoryNum = date_to_category_num(fencer.dateOfBirth, dataStore.currentEvent.opens);
    }
    fencer.registrations = []; // clear out any existing registrations
    fencer.fullGender = fencer.gender == 'M' ? 'M' : 'W';
}