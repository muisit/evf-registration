import { Fencer } from "../../../../../common/api/schemas/fencer";
import { Registration } from "../../../../../common/api/schemas/registration";
import { useDataStore } from "../../../stores/data";

export const ruleEventTeamGrandVeterans = (fencer:Fencer, registration:Registration, competitionRegistrations:Registration[]) => {
    // teams of 3 fencers belonging to age category 60-69 or over
    // with at least 1 fencer from age category 70+
    // and up to 2 reserves (5 total)
    const data = useDataStore();
    let sideEvent = data.sideEventsById['s' + registration.sideEventId];
    if(sideEvent && sideEvent.competition && sideEvent.competition.category && sideEvent.competition.category.abbr == 'T(G)') {
        var team:Registration[]=[];
        var teamname=registration.team;

        // loop over all fencers and pick all registrations with the same team name and category
        competitionRegistrations.map((reg:Registration) => {
            if (reg.sideEventId == registration.sideEventId && reg.team == teamname) {
                team.push(reg);
            }
        })

        if(team.length < 3 || team.length>5) {
            console.log('fencer ', fencer.fullName,' team size is ', team.length);
            registration.errors?.push('Team size incorrect');
        }

        var has_a_cat4_fencer=false;
        var has_a_cat12_fencer=false;
        team.map((reg:Registration) => {
            let f = data.fencerData['f' + reg.fencerId];
            if (f) {
                if(f.categoryNum == 4) has_a_cat4_fencer=true;
                if(f.categoryNum < 3) has_a_cat12_fencer=true;
            }
        });
        if (!has_a_cat4_fencer) {
            console.log('fencer ', fencer.fullName,' missing cat 4');
            registration.errors?.push("Team is missing a category 4 fencer");
        }
        if (has_a_cat12_fencer) {
            console.log('fencer ', fencer.fullName,' has a cat12 fencer');
            registration.errors?.push("Team has a fencer from an invalid category");
        }
    }
}