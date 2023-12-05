import type { Registration } from "../../../../../common/api/schemas/registration";
import type { SideEvent } from "../../../../../common/api/schemas/sideevent";
import type { Fencer } from "../../../../../common/api/schemas/fencer";
import { useDataStore } from "../../../stores/data";
import { is_valid } from "../../../../../common/functions";
import { ruleYoungerCategory } from "./ruleYoungerCategory";
import { ruleCategory } from './ruleCategory';
import { ruleGender } from "./ruleGender";
import { ruleEventTeamGrandVeterans } from './ruleEventTeamGrandVeterans';
import { ruleEventTeamVeterans } from './ruleEventTeamVeterans';
import { allowYoungerCategory } from "../../../../../common/lib/event";

function sortFencers(aId:string, bId:string, data:any, sorter:Array<string>)
{
    var a = data.fencerData[aId];
    var b = data.fencerData[bId];
    if (a && !b) return 1;
    if (b && !a) return -1;
    if (!a && !b) return 0;

    var result = 0;
    sorter.forEach((s:string) => {
        if (result == 0) {
            var v1 = '';
            var v2 = '';
            switch (s) {
                default:
                case 'n': v1 = a.lastName; v2 = b.lastName; break; 
                case 'N': v2 = a.lastName; v1 = b.lastName; break;
                case 'f': v1 = a.firstName; v2 = b.firstName; break; 
                case 'F': v2 = a.firstName; v1 = b.firstName; break;
                case 'y': v1 = a.birthYear; v2 = b.birthYear; break; 
                case 'Y': v2 = a.birthYear; v1 = b.birthYear; break;
                case 'c': v1 = a.category; v2 = b.category; break; 
                case 'C': v2 = a.category; v1 = b.category; break;
                case 'g': v1 = a.fullGender; v2 = b.fullGender; break; 
                case 'G': v2 = a.fullGender; v1 = b.fullGender; break;
            }
            if (v1 > v2) result = 1;
            if (v1 < v2) result = -1;
        }
    });
    return result === 0 ? (a.id > b.id ? 1 : -1) : result;
}

function filterFencers(aId:string, data:any, filterEvents:Array<number>, filterSupportRoles:boolean)
{
    // nothing selected => select all
    if (filterEvents.length == 0 && !filterSupportRoles) return true;
    var a = data.fencerData[aId];

    var retval = false;
    if (a && a.registrations) {
        a.registrations.forEach((reg:Registration) => {
            retval = retval || filterEvents.includes(reg.sideEventId || 0);

            if (filterSupportRoles && is_valid(reg.roleId)) {
                retval = true;
            }
        });
    }
    return retval;
}

function validateFencerState(fencer:Fencer, sepByEvent:any, youngerCategory:boolean)
{
    fencer.registrations = fencer.registrations?.map((reg:Registration) => {
        reg.errors = [];
        if (youngerCategory) {
            ruleYoungerCategory(fencer, reg, sepByEvent['s' + reg.sideEventId]);
        }
        else {
            ruleCategory(fencer, reg, sepByEvent['s' + reg.sideEventId]);
        }
        ruleGender(fencer, reg, sepByEvent['s' + reg.sideEventId]);
        ruleEventTeamGrandVeterans(fencer, reg, sepByEvent['s' + reg.sideEventId]);
        ruleEventTeamVeterans(fencer, reg, sepByEvent['s' + reg.sideEventId]);
        return reg;
    });

    return fencer;
}

interface RegistrationsById {
    [key:string]: Registration[];
}

export function sortAndFilterFencers(sorters:Array<string>, filters:Array<string>)
{
    const data = useDataStore();
    let allow_registration_lower_age = allowYoungerCategory(data.currentEvent);

    var filterEvents:Array<number> = [];
    data.competitionEvents.forEach((se:SideEvent) => {
        if (se.competition && se.competition.weapon && filters.includes(se.competition.weapon.abbr || '')) {
            filterEvents.push(se.id);
        }
    });
    data.nonCompetitionEvents.forEach((se:SideEvent) => {
        if (filters.includes(se.abbr || '')) {
            filterEvents.push(se.id);
        }
    });

    var keylist = Object.keys(data.fencerData)
        .filter((aId) => filterFencers(aId, data, filterEvents, filters.includes('Support')))
        .sort((aId, bId) => sortFencers(aId, bId, data, sorters));

    var retval:Array<Fencer> = [];
    keylist.forEach((id) => {
        retval.push(data.fencerData[id]);
    });
    var sepByEvent:RegistrationsById = {};
    retval.map((fencer:Fencer) => {
        if (fencer.registrations && fencer.registrations.length) {
            fencer.registrations.map((reg:Registration) => {
                let sideEvent = data.sideEventsById['s' + reg.sideEventId];
                if (sideEvent && sideEvent.competition) {
                    let key = 's' + sideEvent.id;
                    if (!sepByEvent[key]) {
                        sepByEvent[key] = [];
                    }
                    sepByEvent[key].push(reg);
                }
            });
        }
    })
    return retval.map((fencer:Fencer) => validateFencerState(fencer, sepByEvent, allow_registration_lower_age));
}