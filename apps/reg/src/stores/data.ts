import { ref } from 'vue'
import { defineStore } from 'pinia'
import { is_valid } from '../../../common/functions';
import { basicData } from '../../../common/api/basicdata';
import { eventlist } from '../../../common/api/event/eventlist';
import { overview } from '../../../common/api/event/overview';
import { registrations } from '../../../common/api/event/registrations';
import { abbreviateSideEvent } from './lib/abbreviateSideEvent';
import { overviewToCountry } from './lib/overviewToCountry';
import { registrationToFencers } from './lib/registrationToFencers';
import { SideEvent } from '../../../common/api/schemas/sideevent';
import { Competition } from '../../../common/api/schemas/competition';

export const useDataStore = defineStore('data', () => {
    const categories = ref([]);
    const categoriesById = ref({});
    const roles = ref([]);
    const countryRoles = ref([]);
    const organisationRoles = ref([]);
    const officialRoles = ref([]);
    const rolesById = ref({});
    const weapons = ref([]);
    const weaponsById = ref({});
    const countries = ref([]);
    const countriesById = ref({});

    const currentEvent = ref({id: -1, title: 'Please wait while loading', competitions: [], sideEvents: []});
    const events = ref([]);
    const competitions = ref([]);
    const competitionsById = ref({});
    const sideEvents = ref([]);
    const competitionEvents = ref([]);
    const nonCompetitionEvents = ref([]);
    const sideEventsById = ref({});

    const overviewData = ref([]);
    const overviewPerCountry = ref([]);

    const currentCountry = ref({id: 0, name: 'Organisation', abbr: 'Org'});
    const fencerData = ref({});

    function hasBasicData() {
        return categories.value.length > 0;
    }

    function getBasicData() {
        if (!hasBasicData()) {
            return basicData()
                .then((data) => {
                    fillData(data);
                })
                .catch((e) => {
                    console.log(e);
                    setTimeout(() => { getBasicData(); }, 500);
                });
        }
        else {
            return Promise.resolve();
        }
    }

    function fillData(data:object) {
        categories.value = [];
        categoriesById.value = {};
        roles.value = [];
        officialRoles.value = [];
        organisationRoles.value = [];
        countryRoles.value = [];
        rolesById.value = {};
        weapons.value = [];
        weaponsById.value = {};
        countries.value = [];
        countriesById.value = {};

        if (data.categories) {
            categories.value = data.categories;
            categories.value.forEach((item) => {
                categoriesById.value['c' + item.id] = item;
            });
        }

        if (data.weapons) {
            weapons.value = data.weapons;
            weapons.value.forEach((item) => {
                weaponsById.value['w' + item.id] = item;
            });
        }

        if (data.countries) {
            countries.value = data.countries;
            countries.value.forEach((item) => {
                countriesById.value['c' + item.id] = item;
            });
        }

        if (data.roles) {
            roles.value = data.roles;
            roles.value.forEach((item) => {
                rolesById.value['r' + item.id] = item;

                switch(item.type) {
                    case 'Org': organisationRoles.value.push(item); break;
                    case 'FIE':
                    case 'EVF': officialRoles.value.push(item); break;
                    default: countryRoles.value.push(item); break;
                }
            });
        }
    }

    function getEvents() {
        eventlist()
            .then((data) => {
                events.value = data;
                if (events.value && events.value.length > 0) {
                    setEvent(events.value[0].id);
                }
            });
    }

    function setEvent(eventId:number) {
        console.log('setting event', eventId);
        if (!is_valid(eventId)) return;

        var eventFound = { id: -1, title: 'Please wait while loading', competitions: [], sideEvents: []};
        events.value.forEach((data) => {
            if (data.id == eventId) {
                eventFound = data;
            }
        });

        console.log('mapping competitions for new event');
        currentEvent.value = eventFound;
        competitions.value = eventFound.competitions.map((comp:Competition) => {
            if (is_valid(comp.categoryId)) {
                comp.category = categoriesById.value['c' + comp.categoryId];
            }
            if (is_valid(comp.weaponId)) {
                comp.weapon = weaponsById.value['w' + comp.weaponId];
            }
            return comp;
        });
        competitionsById.value = {};
        competitions.value.forEach((data) => competitionsById.value['c' + data.id] = data);

        console.log('mapping side events for new event');
        // sort side events in competion based events and side events
        var comps = [];
        var ses = [];
        eventFound.sideEvents.map((se:SideEvent) => {
            if (is_valid(se.competitionId)) {
                se.competition = competitionsById.value['c' + se.competitionId];
                comps.push(se);
            }
            else {
                ses.push(se);
            }
            se.abbr = abbreviateSideEvent(se);
        });
        competitionEvents.value = comps;
        nonCompetitionEvents.value = ses;
        sideEvents.value = comps.concat(ses);
        sideEventsById.value = {};
        sideEvents.value.forEach((data) => sideEventsById.value['s' + data.id] = data);

        overviewData.value = [];
        fencerData.value = {};
    } 

    function getOverview() {
        if (!is_valid(currentEvent.value)) return [];

        overview(currentEvent.value.id)
            .then((data) => {
                console.log('setting overviewData', data);
                overviewData.value = data;
                overviewPerCountry.value = overviewToCountry(data);
                return data;
            }, (e) => {
                console.log(e);
                alert("There was an error retrieving the general overview. Please reload the page. If this problem persists, please contact the webmaster");
            })
            .catch((e) => {
                console.log(e);
                alert("There was an error retrieving the general overview. Please reload the page. If this problem persists, please contact the webmaster");
            });
    }

    function setCountry(cid:number) {
        console.log('setting country to ', cid);
        if (!is_valid(cid) || !countriesById.value['c' + cid]) {
            console.log('this is the organisation');
            currentCountry.value = {id: 0, name: 'Organisation', abbr:'Org'};
        }
        else {
            console.log('this is a valid country');
            currentCountry.value = countriesById.value['c' + cid];
        }
        fencerData.value = {};
        console.log('receiving registrations because country has changed', currentCountry.value, cid);
        getRegistrations();
    }

    function getRegistrations() {
        console.log('getting registrations', currentCountry.value);
        var cid = currentCountry.value.id;
        if (!is_valid(cid)) cid = 0;
        registrations(currentEvent.value.id, cid)
            .then((data) => {
                console.log('received registrations, creating data structure', data);
                registrationToFencers(data);
                console.log('fencerdata should have changed now', Object.keys(fencerData.value).length);
            }, (e) => {
                console.log(e);
                alert("There was an error retrieving the registration data. Please reload the page. If this problem persists, please contact the webmaster");
            })
            .catch((e) => {
                console.log(e);
                alert("There was an error retrieving the registration data. Please reload the page. If this problem persists, please contact the webmaster");
            });
    }

    function fencerList(sorter:Array<string>)
    {
        console.log('creating list of fencers, sorted', sorter, Object.keys(fencerData.value).length);
        var keylist = Object.keys(fencerData.value).sort((aId, bId) => {
            var a = fencerData.value[aId];
            var b = fencerData.value[bId];
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
        });

        var retval = [];
        keylist.forEach((id) => {
            retval.push(fencerData.value[id]);
        });
        return retval;
    }

    return {
        categories, categoriesById,
        roles, rolesById, countryRoles, organisationRoles, officialRoles,
        weapons, weaponsById,
        countries, countriesById,
        fillData, getBasicData, hasBasicData,

        events, currentEvent,
        competitions, competitionsById,
        sideEvents, sideEventsById, competitionEvents, nonCompetitionEvents,
        getEvents, setEvent,
        
        overviewData, overviewPerCountry,
        getOverview,

        currentCountry, fencerData,
        setCountry, getRegistrations, fencerList,
    }
})
