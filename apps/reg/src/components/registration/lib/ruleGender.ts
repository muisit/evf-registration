import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../../common/api/schemas/registration";
import { useDataStore } from "../../../stores/data";

export const ruleGender = (fencer:Fencer, registration:Registration, competitionRegistrations:Registration[]) => {
    const data = useDataStore();
    let sideEvent = data.sideEventsById['s' + registration.sideEventId];
    if(sideEvent && sideEvent.competition && sideEvent.competition.weapon) {
        if (!fencer.gender || fencer.gender != sideEvent.competition.weapon.gender) {
            registration.errors?.push("Fencer has the wrong gender for this competition");
        }
    }
}