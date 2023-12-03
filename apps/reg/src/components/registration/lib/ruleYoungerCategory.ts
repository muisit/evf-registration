import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../../common/api/schemas/registration";
import { useDataStore } from "../../../stores/data";

export const ruleYoungerCategory = (fencer:Fencer, registration:Registration, competitionRegistrations:Registration[]) => {
    const data = useDataStore();
    let sideEvent = data.sideEventsById['s' + registration.sideEventId];
    if(sideEvent && sideEvent.competition && sideEvent.competition.category && sideEvent.competition.category.type != 'T') {
        let categoryValue = sideEvent.competition.category.value || 0;
        if (!fencer.categoryNum || fencer.categoryNum < categoryValue) {
            registration.errors?.push("Fencer is too young for this competition");
        }
    }
}