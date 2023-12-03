import type { Fencer } from "../../../../../common/api/schemas/fencer";
import type { SideEvent, SideEventById } from "../../../../../common/api/schemas/sideevent";
import type { RoleSchema } from "../../../../../common/api/schemas/role";
import type { StringKeyedNumber } from "../../../../../common/types";
import { useDataStore } from "../../../stores/data";
import { selectRolesForFencer } from "./selectRolesForFencer";
import { filterEventTeamGrandVeterans } from "./filterEventTeamGrandVeterans";
import { filterEventTeamVeterans } from "./filterEventTeamVeterans";
import { filterEventCategory } from "./filterEventCategory";
import { filterEventCategoryYounger } from "./filterEventCategoryYounger";
import { is_valid } from "../../../../../common/functions";
import { allowYoungerCategory } from "../../../../../common/lib/event";

function fencerIsRegisteredForEvent(fencer:Fencer, event:SideEvent)
{
    if (!fencer.registrations || fencer.registrations.length == 0) return false;

    for (let i = 0; i< fencer.registrations?.length; i++) {
        if (is_valid(fencer.registrations[i].sideEventId) && fencer.registrations[i].sideEventId == event.id) {
            return true;
        }
    }
    return false;
}

interface EventsById {
    [key:string]: SideEvent[];
}

export function selectEventsForFencer(fencer:Fencer) {
    const data = useDataStore();
    let allow_registration_lower_age = allowYoungerCategory(data.currentEvent);

    // filter the available events based on category and gender
    let events:SideEvent[] = [];

    // filter out valid roles for the capabilities
    let roles:RoleSchema[] = selectRolesForFencer(fencer);

    if (fencer && is_valid(fencer.id) && is_valid(data.currentEvent.id)) {
        let weaponevents:SideEventById = {}; // stores the events qualified for this fencer based on weapon
        let allweaponevents:EventsById = {}; // stores all events based on weapon

        events = data.sideEvents.map((event:SideEvent) => {
            event.isAthleteEvent = false; // is this a competition event selectable for this specific athlete
            event.isTeamEvent = false; // is this a competition event selectable for this specific athlete AND a team event
            event.defaultRole = null; // regular participant
            event.isNonCompetitionEvent = false; // is this a non-competition event
            event.isRegistered = fencerIsRegisteredForEvent(fencer, event);

            if (event.competition) {
                event.defaultRole = '' + roles[0].id; // any non-athlete role
                if (event.competition.category && event.competition.weapon) { // should always be true
                    let key = event.competition.weapon.abbr || '';
                    event.isAthleteEvent = filterEventCategory(fencer,event)
                            || filterEventTeamVeterans(fencer,event)
                            || filterEventTeamGrandVeterans(fencer,event);
                    if(event.isAthleteEvent) {
                        event.defaultRole = null;
                        if(event.competition.category.type == 'T') {
                            event.isTeamEvent = true;
                            // allow individual and team events in the same tournament by prefixing with 'T'
                            weaponevents["T" + key] = event; 
                        }
                        else {
                            weaponevents[key] = event;
                        }
                    }
                    if(allow_registration_lower_age && filterEventCategoryYounger(fencer, event)) {
                        // create a list of all events that match gender and are for a younger category
                        if(!allweaponevents[key]) allweaponevents[key]=[];
                        allweaponevents[key].push(event);
                    }
                }
            }
            else {
                // not an athlete event
                event.isNonCompetitionEvent = true;
            }
            return event;
        });

        // see if we need to open up events of a younger category
        if(allow_registration_lower_age) {
            let openevents:StringKeyedNumber = {};
            for (let i in allweaponevents) {
                // look only in events that have no athlete-weapon-event for this fencer
                if(!weaponevents[i]) {
                    // check all events and pick the one for the highest category
                    // only events that are for younger categories are listed at this point
                    let highestcat = -1;
                    for(var j in allweaponevents[i]) {
                        var event = allweaponevents[i][j];
                        if(event.competition && event.competition.category && event.competition.category.value !== null) {
                            if (event.competition.category.value > highestcat) {
                                highestcat = event.competition.category.value || 0;
                            }
                        }
                    }
                    openevents[i] = highestcat;
                }
            }

            // now set the is_athlete flag on the events we need to open for a younger category as well
            events = events.map((event) => {
                if (event.competition && event.competition.category && event.competition.weapon) {
                    let key = event.competition.weapon.abbr || '';
                    var hasOwnCat = openevents[key];
                    if(hasOwnCat && event.competition.weapon.gender == fencer.gender && event.competition.category.value == hasOwnCat) {
                        event.isAthleteEvent = true;
                        event.defaultRole=null; // default role for athlete events is athlete
                    }
                }
                return event;
            });
        }
    }
    return events;
}
