import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { Registration } from "../../../../../common/api/schemas/registration";
import { useDataStore } from "../../../stores/data";

export const ruleEventTeamVeterans = (fencer:Fencer, registration:Registration, competitionRegistrations:Registration[]) => {
    // teams of 3 fencers belonging to age category 40-60 (1 and 2)
    // with at least 1 fencer from age category 50-60
    // and up to 2 reserves (5 total)
    const data = useDataStore();
    let sideEvent = data.sideEventsById['s' + registration.sideEventId];
    if(sideEvent && sideEvent.competition && sideEvent.competition.category && sideEvent.competition.category.abbr == 'T') {
        var team:Registration[]=[];
        var teamname=registration.team;

        // loop over all fencers and pick all registrations with the same team name and category
        competitionRegistrations.map((reg:Registration) => {
            if (reg.sideEventId == registration.sideEventId && reg.team == teamname) {
                team.push(reg);
            }
        })

        if(team.length < 3 || team.length>5) {
            registration.errors?.push('Team size incorrect');
        }

        var has_a_cat2_fencer=false;
        var has_a_cat34_fencer=false;
        team.map((reg:Registration) => {
            let f = data.fencerData['f' + reg.fencerId];
            if (f) {
                let categoryValue = f.categoryNum || 0;
                if(categoryValue == 2) has_a_cat2_fencer=true;
                if(categoryValue > 2 || categoryValue < 1) has_a_cat34_fencer=true;
            }
        });
        if (!has_a_cat2_fencer) {
            registration.errors?.push("Team is missing a category 2 fencer");
        }
        if (has_a_cat34_fencer) {
            registration.errors?.push("Team has a fencer from an invalid category");
        }
    }
}