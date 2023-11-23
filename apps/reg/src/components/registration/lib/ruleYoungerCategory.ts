import { Fencer } from "../../../../../common/api/schemas/fencer";
import { Registration } from "../../../../../common/api/schemas/registration";
import { useDataStore } from "../../../stores/data";

export const ruleYoungerCategory = (fencer:Fencer, registration:Registration, competitionRegistrations:Registration[]) => {
    const data = useDataStore();
    let sideEvent = data.sideEventsById['s' + registration.sideEventId];
    if(sideEvent && sideEvent.competition && sideEvent.competition.category && sideEvent.competition.category.type != 'T') {
        if (!fencer.categoryNum || fencer.categoryNum < sideEvent.competition.category.value) {
            console.log('fencer ', fencer.fullName,' is in wrong (older) category', fencer.categoryNum, sideEvent.competition.categoryNum);
            registration.errors?.push("Fencer is too young for this competition");
        }
    }
}