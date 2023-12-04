import type { FencerById } from '../../../../../common/api/schemas/fencer';
import type { SideEvent } from '../../../../../common/api/schemas/sideevent';
import type { Registration } from '../../../../../common/api/schemas/registration';
import type { SideEventById } from '../../../../../common/api/schemas/sideevent';

interface TeamNameObject {
    [key:string]: boolean;
}

interface TeamNamesObject {
    [key:string]: TeamNameObject;
}

interface TeamNames {
    [key:string]:string[];
}

export function determineUniqueTeamNames(fencers:FencerById, events:SideEvent[]): TeamNames
{
    let teamNamesObject:TeamNamesObject = {};
    let eventsById:SideEventById = {};
    events.map((event:SideEvent) => { 
        eventsById['s' + event.id] = event;
        return event.id;
    });

    Object.keys(fencers).map((fid:string) => {
        let fencer = fencers[fid];
        if (fencer && fencer.registrations) {
            fencer.registrations?.map((reg:Registration) =>{
                if (eventsById['s' + reg.sideEventId] && reg.team !== null) {
                    let sideEvent = eventsById['s' + reg.sideEventId];
                    if (sideEvent.competition) {
                        let key = sideEvent.competition.weapon?.name || '';
                        if (!teamNamesObject[key]) {
                            teamNamesObject[key] = {};
                        }
                        teamNamesObject[key][reg.team] = true;
                    }
                }
            });
        }
    });
    let teamNames:TeamNames = {};
    Object.keys(teamNamesObject).map((weaponId:string) => {
        teamNames[weaponId] = Object.keys(teamNamesObject[weaponId]);
    });
    return teamNames;
}
