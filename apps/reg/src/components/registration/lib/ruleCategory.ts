import { Fencer } from "../../../../../common/api/schemas/fencer";
import { Registration } from "../../../../../common/api/schemas/registration";
import { useDataStore } from "../../../stores/data";

export const ruleCategory = (fencer:Fencer, registration:Registration, competitionRegistrations:Registration[]) => {
    const data = useDataStore();
    let sideEvent = data.sideEventsById['s' + registration.sideEventId];
    if(sideEvent && sideEvent.competition && sideEvent.competition.category) {
        if (!fencer.categoryNum || fencer.categoryNum != sideEvent.competition.category.value && sideEvent.competition.category.type != 'T') {
            registration.errors?.push("Fencer is in the wrong category");
        }
    }
}