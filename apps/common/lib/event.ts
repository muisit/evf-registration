import type{ Event } from '../api/schemas/event';
import { parse_date } from '../functions';

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

export function generateAccreditations(event:Event|null)
{
    if (event && event.config && event.config.no_accreditations == false) {
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

export function isOpenForRegistration(event:Event)
{
    const now = parse_date();
    const opens = parse_date(event.reg_open);
    const closes = parse_date(event.reg_close);
    return opens.isBefore(now) && closes.add(1, 'day').isAfter(now);
}

export function isOpenForRegistrationView(event:Event)
{
    const now = parse_date();
    const opens = parse_date(event.reg_open);
    const starts = parse_date(event.opens);
    const finishes = starts.add((event.duration || 0) + 2, 'day');
    return opens.isBefore(now) && finishes.isAfter(now);
}