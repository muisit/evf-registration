import { ref } from 'vue'
import { defineStore } from 'pinia'
import { is_valid } from '../../../common/functions';
import { basicData } from '../../../common/api/basicdata';
import { eventlist } from '../../../common/api/event/eventlist';
import { overview } from '../../../common/api/event/overview';
import { abbreviateSideEvent } from './lib/abbreviateSideEvent';
import { overviewToCountry } from './lib/overviewToCountry';
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
            se.abbr = abbreviateSideEvent(se, this);
        });
        competitionEvents.value = comps;
        nonCompetitionEvents.value = ses;
        sideEvents.value = comps.concat(ses);
        sideEventsById.value = {};
        sideEvents.value.forEach((data) => sideEventsById.value['s' + data.id] = data);

        console.log('resetting overview data');
        overviewData.value = [];
    } 

    function getOverview() {
        if (!is_valid(currentEvent.value)) return [];

        console.log("requesting overviewData");
        overview(currentEvent.value.id)
            .then((data) => {
                console.log('setting overviewData', data);
                overviewData.value = data;
                overviewPerCountry.value = overviewToCountry(data, this);
                return data;
            });
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

    }
})
