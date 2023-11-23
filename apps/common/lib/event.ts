import { Event } from '../api/schemas/event';

export function allowMoreTeams(event:Event|null)
{
    if (event && event.config && event.config.allow_more_teams) {
        return true;
    }
    return false;
}

export function allowYoungerCategory(event:Event|null)
{
    if (event && event.config && event.config.allow_registration_lower_age) {
        return true;
    }
    return false;
}

export function hasTeam(event:Event|null)
{
    let hasTeams = false;
    if (event && event.competitions && event.competitions.length) {
        event.competitions.map((c) => {
            if (c.category?.type == 'T') {
                hasTeams = true;
            }
        });
    }
    return hasTeams;
}
