import type { AccreditationOverviewLine } from "../../../../common/api/schemas/accreditation";
import type { TemplateSchema } from "../../../../common/api/schemas/template";
import { useDataStore } from "../data";
import type { BadgeOverview, CountPerCountry, CountPerEvent, CountPerRole, CountPerTemplate } from "./accreditationtypes";

export function parseAccreditationOverview(lines:AccreditationOverviewLine[]): BadgeOverview
{
    let retval:BadgeOverview = {countries:[], events:[], templates:[], roles:[]};
    const data = useDataStore();

    lines.forEach((line:AccreditationOverviewLine) => {
        switch(line.type) {
            case 'E':
                let skey = 's' + line.id;
                if (data.sideEventsById[skey]) {
                    let countPerEvent:CountPerEvent = {
                        sideEvent: data.sideEventsById[skey],
                        registrations: line.counts[0] || 0,
                        accreditations: line.counts[1] || 0,
                        dirty: line.counts[2] || 0,
                        generated: line.counts[3] || 0,
                        documents: line.documents
                    };
                    retval.events.push(countPerEvent);
                }
                break;
            case 'C':
                let ckey = 'c' + line.id;
                if (data.countriesById[ckey]) {
                    let countPerCountry:CountPerCountry = {
                        country: data.countriesById[ckey],
                        registrations: line.counts[0] || 0,
                        accreditations: line.counts[1] || 0,
                        dirty: line.counts[2] || 0,
                        generated: line.counts[3] || 0,
                        documents: line.documents
                    };
                    retval.countries.push(countPerCountry);
                }
                break;
            case 'R':
                let rkey = 'r' + line.id;
                if (data.rolesById[rkey]) {
                    let countPerRole:CountPerRole = {
                        role: data.rolesById[rkey],
                        registrations: line.counts[0] || 0,
                        accreditations: line.counts[1] || 0,
                        dirty: line.counts[2] || 0,
                        generated: line.counts[3] || 0,
                        documents: line.documents
                    };
                    retval.roles.push(countPerRole);
                }
                break;
            case 'T':
                if (data.currentEvent.templates) {
                    data.currentEvent.templates.forEach((template:TemplateSchema) => {
                        if (template.id == line.id) {
                            let countPerTemplate:CountPerTemplate = {
                                template: template,
                                registrations: line.counts[0] || 0,
                                accreditations: line.counts[1] || 0,
                                dirty: line.counts[2] || 0,
                                generated: line.counts[3] || 0,
                                documents: line.documents
                            };
                            retval.templates.push(countPerTemplate);
                        }
                    });
                }
                break;
            default:
                console.log('ERROR: no such type defined: ', line);
                break;
        }
    });
    return retval;
}