import { is_valid } from "../../../../common/functions";
import type { OverviewLine, OverviewObjects } from "../../../../common/api/schemas/overviewline";
import { useDataStore } from "../data";


export function overviewToCountry(overviewData: Array<OverviewLine>)
{
    var retval:OverviewObjects = {};
    if (!overviewData) return {};
    const dataStore = useDataStore();

    overviewData.forEach((line) => {
        var ckey = line.country;
        var country = dataStore.countriesById[ckey];
        if (is_valid(country)) {
            if (!retval[ckey]) {
                retval[ckey] = { country: country, events: {}};
            }

            Object.keys(line.counts).map((skey:string) => {
                var sideEvent = dataStore.sideEventsById[skey];
                if (is_valid(sideEvent) || skey == 'ssup') {
                    if (!retval[ckey].events[skey]) {
                        retval[ckey].events[skey] = {
                            sideEvent: sideEvent,
                            participants: 0,
                            teams: 0
                        };
                    }
                    var countList = line.counts[skey];
                    retval[ckey].events[skey].participants = countList[0];
                    retval[ckey].events[skey].teams = countList[1];
                }
            });
        }
    });
    return retval;
}