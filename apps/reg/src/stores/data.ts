import type { Ref } from 'vue'
import type { Event } from '../../../common/api/schemas/event';
import type { SideEvent, SideEventById } from '../../../common/api/schemas/sideevent';
import type { CategorySchema, CategoryById } from '../../../common/api/schemas/category';
import type { Fencer, FencerById } from '../../../common/api/schemas/fencer';
import type { Competition, CompetitionById } from '../../../common/api/schemas/competition';
import type { CountrySchema, CountryById } from '../../../common/api/schemas/country';
import type { Registration } from '../../../common/api/schemas/registration';
import type { RoleSchema, RoleById } from '../../../common/api/schemas/role';
import type { WeaponSchema, WeaponById } from '../../../common/api/schemas/weapon';
import type { OverviewLine, OverviewObjects } from '../../../common/api/schemas/overviewline';
import { ref } from 'vue'
import { defineStore } from 'pinia'
import { is_valid } from '../../../common/functions';
import { basicData } from '../../../common/api/basicdata';
import { eventlist } from '../../../common/api/event/eventlist';
import { overview } from '../../../common/api/event/overview';
import { saveregistration } from '../../../common/api/registrations/saveregistration';
import { deleteregistration } from '../../../common/api/registrations/deleteregistration';
import { registrations } from '../../../common/api/registrations/registrations';
import { abbreviateSideEvent } from './lib/abbreviateSideEvent';
import { overviewToCountry } from './lib/overviewToCountry';
import { registrationToFencers } from './lib/registrationToFencers';
import { insertFencer } from './lib/insertFencer';
import { defaultEvent } from '../../../common/api/schemas/event';
import { useAuthStore } from '../../../common/stores/auth';
import { payregistration } from '../../../common/api/registrations/payregistration';
import { findRegistration } from './lib/findRegistration';
import { addRegistration } from './lib/addRegistration';
import { updateRegistration } from './lib/updateRegistration';
import { deleteRegistration } from './lib/deleteRegistration';

export const useDataStore = defineStore('data', () => {
    const categories:Ref<CategorySchema[]> = ref([]);
    const categoriesById:Ref<CategoryById> = ref({});
    const roles:Ref<RoleSchema[]> = ref([]);
    const countryRoles:Ref<RoleSchema[]> = ref([]);
    const organisationRoles:Ref<RoleSchema[]> = ref([]);
    const officialRoles:Ref<RoleSchema[]> = ref([]);
    const rolesById:Ref<RoleById> = ref({});
    const weapons:Ref<WeaponSchema[]> = ref([]);
    const weaponsById:Ref<WeaponById> = ref({});
    const countries:Ref<CountrySchema[]> = ref([]);
    const countriesById:Ref<CountryById> = ref({});

    const currentEvent:Ref<Event> = ref(defaultEvent());
    const events:Ref<Event[]> = ref([]);
    const competitions:Ref<Competition[]> = ref([]);
    const competitionsById:Ref<CompetitionById> = ref({});
    const sideEvents:Ref<SideEvent[]> = ref([]);
    const competitionEvents:Ref<SideEvent[]> = ref([]);
    const nonCompetitionEvents:Ref<SideEvent[]> = ref([]);
    const sideEventsById:Ref<SideEventById> = ref({});

    const overviewData:Ref<OverviewLine[]> = ref([]);
    const overviewPerCountry:Ref<OverviewObjects> = ref({});

    const currentCountry:Ref<CountrySchema> = ref({id: 0, name: 'Organisation', abbr: 'Org', path: ''});
    const fencerData:Ref<FencerById> = ref({});
    const temporaryRegistrationId = ref(-1);

    function hasBasicData() {
        return categories.value.length > 0;
    }

    function getBasicData(callback:Function) {
        if (!hasBasicData()) {
            const authStore = useAuthStore();
            authStore.isLoading('basic');
            return basicData()
                .then((data) => {
                    authStore.hasLoaded('basic');
                    fillData(data);
                    callback();
                })
                .catch((e) => {
                    authStore.hasLoaded('basic');
                    console.log(e);
                    setTimeout(() => { getBasicData(callback); }, 500);
                });
        }
        else {
            return Promise.resolve();
        }
    }

    function fillData(data:any) {
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

    function getEvents(eventid?:string) {
        const authStore = useAuthStore();
        authStore.isLoading('events');
        return eventlist()
            .then((data) => {
                authStore.hasLoaded('events');
                events.value = data;
                if (events.value && events.value.length > 0) {
                    let eid = parseInt(eventid || '0');
                    let validIds = events.value.map((e:Event) => e.id);
                    if (!is_valid(eid) || !validIds.includes(eid)) {
                        eid = events.value[0].id || 0;
                    }
                    setEvent(eid);
                }
            })
            .catch((e) => {
                authStore.hasLoaded('events');
            })
    }

    function setEvent(eventId:number) {
        if (!is_valid(eventId)) {
            console.log("no valid event id for setEvent");
            return;
        }

        var eventFound:Event = defaultEvent();
        events.value.forEach((data) => {
            if (data.id == eventId) {
                eventFound = data;
            }
        });

        currentEvent.value = eventFound;
        competitions.value = [];
        competitionsById.value = {};
        competitionEvents.value = [];
        nonCompetitionEvents.value = [];
        sideEvents.value = [];
        sideEventsById.value = {};

        if (eventFound.competitions) {
            competitions.value = eventFound.competitions.map((comp:Competition) => {
                if (is_valid(comp.categoryId)) {
                    comp.category = categoriesById.value['c' + comp.categoryId];
                }
                if (is_valid(comp.weaponId)) {
                    comp.weapon = weaponsById.value['w' + comp.weaponId];
                }
                return comp;
            });
        }
        competitions.value.forEach((data) => competitionsById.value['c' + data.id] = data);

        // sort side events in competition based events and side events
        var comps:SideEvent[] = [];
        var ses:SideEvent[] = [];
        if (eventFound.sideEvents) {
            eventFound.sideEvents.map((se:SideEvent) => {
                if (is_valid(se.competitionId)) {
                    se.competition = competitionsById.value['c' + se.competitionId];
                    //se.title = se.competition?.weapon?.name + " " + se.competition?.category.name;
                    comps.push(se);
                }
                else {
                    ses.push(se);
                }
                se.abbr = abbreviateSideEvent(se);
            });
        }
        competitionEvents.value = comps;
        nonCompetitionEvents.value = ses;
        sideEvents.value = comps.concat(ses);
        sideEvents.value.forEach((data) => sideEventsById.value['s' + data.id] = data);

        overviewData.value = [];
        fencerData.value = {};

        const authStore = useAuthStore();
        authStore.eventId = currentEvent.value.id || 0;
    } 

    function getOverview():Promise<OverviewLine[]> {
        if (!is_valid(currentEvent.value)) {
            console.log("no valid event, returning empty promise");
            return new Promise(() => []);
        }

        const authStore = useAuthStore();
        authStore.isLoading('overview');
        return overview()
            .then((data:OverviewLine[]) => {
                authStore.hasLoaded('overview');
                overviewData.value = data;
                overviewPerCountry.value = overviewToCountry(data);
                return data;
            }, (e) => {
                authStore.hasLoaded('overview');
                console.log(e);
                alert("There was an error retrieving the general overview. Please reload the page. If this problem persists, please contact the webmaster");
                return [];
            })
            .catch((e) => {
                authStore.hasLoaded('overview');
                console.log(e);
                alert("There was an error retrieving the general overview. Please reload the page. If this problem persists, please contact the webmaster");
                return [];
            });
    }

    function setCountry(cid:number) {
        if (!is_valid(cid) || !countriesById.value['c' + cid]) {
            currentCountry.value = {id: 0, name: 'Organisation', abbr:'Org', path: ''};
        }
        else {
            currentCountry.value = countriesById.value['c' + cid];
        }
        fencerData.value = {};

        const authStore = useAuthStore();
        if (authStore.canSwitchCountry()) {
            authStore.countryId = currentCountry.value.id || 0;
        }
    }

    function getRegistrations() {
        const authStore = useAuthStore();
        authStore.isLoading('registrations');
        return registrations()
            .then((data) => {
                authStore.hasLoaded('registrations');
                registrationToFencers(data);
            }, (e) => {
                authStore.hasLoaded('registrations');
                console.log(e);
                alert("There was an error retrieving the registration data. Please reload the page. If this problem persists, please contact the webmaster");
            })
            .catch((e) => {
                authStore.hasLoaded('registrations');
                console.log(e);
                alert("There was an error retrieving the registration data. Please reload the page. If this problem persists, please contact the webmaster");
            });
    }

    function addFencer(fencer:Fencer)
    {
        insertFencer(fencer);
    }

    function saveRegistration(pFencer:Fencer, sideEvent:SideEvent|null, roleId:number|null, teamName:string|null, payment:string|null)
    {
        let registration = createOrFindRegistration(pFencer, sideEvent, roleId, teamName, payment);
        registration.state = 'saving';
        fencerData.value = updateRegistration(fencerData.value, registration);

        if (registration) {
            saveregistration(registration).then((data) => {
                    if (data && is_valid(data.id)) {
                        registration.state = 'saved';
                        // use a callback to update the back-end id
                        fencerData.value = updateRegistration(fencerData.value, registration, (old:Registration, nw:Registration) => {
                            nw.id = data.id;
                            return nw;
                        });

                        window.setTimeout(() => {
                            // only adjust the state if it is still 'saved', or else we may be clicking quickly
                            fencerData.value = updateRegistration(fencerData.value, registration, (old:Registration, nw: Registration) => {
                                if (old.state == 'saved') {
                                    old.state = '';
                                }
                                return old;
                            });
                        }, 3000);
                    }
                    else {
                        console.log('error on save of registration for ', pFencer.id, sideEvent?.id, roleId);
                        registration.state = 'error';
                        fencerData.value = updateRegistration(fencerData.value, registration);
                    }
                })
                .catch((e) => {
                    console.log(e);
                    console.log('error on save of registration for ', pFencer.id, sideEvent?.id, roleId);
                    registration.state = 'error';
                    fencerData.value = updateRegistration(fencerData.value, registration);
            });
        }
    }

    function createOrFindRegistration(fencer:Fencer, sideEvent:SideEvent|null, roleId:number|null, teamName:string|null, payment:string|null)
    {
        let registration = findRegistrationForFencerEventAndRole(fencer, sideEvent, roleId);
        if (!registration) {
            registration = {
                id: temporaryRegistrationId.value,
                fencerId: fencer.id,
                roleId: is_valid(roleId) ? roleId : null,
                sideEventId: sideEvent ? sideEvent.id : null,
                dateTime: null,
                payment: payment,
                paid: null,
                paidHod: null,
                team: teamName,
                state: ''
            };
            temporaryRegistrationId.value -= 1;
            fencerData.value = addRegistration(fencerData.value, registration);
        }
        return registration;
    }

    function findRegistrationForFencerEventAndRole(pFencer:Fencer, sideEvent:SideEvent|null, roleId:number|null):Registration|null
    {
        return findRegistration(fencerData.value, pFencer, sideEvent, roleId);
    }

    function removeRegistration(pFencer:Fencer, sideEvent:SideEvent|null, roleId:number|null)
    {
        let registration = findRegistrationForFencerEventAndRole(pFencer, sideEvent, roleId);

        if (registration !== null) {
            registration.state = 'removing';
            fencerData.value = updateRegistration(fencerData.value, registration);

            deleteregistration(registration.id || 0)
                .then((data) => {
                    if (data && data.status == 'ok' && registration) {
                        registration.state = 'removed';
                        fencerData.value = updateRegistration(fencerData.value, registration);
                        window.setTimeout(() => {
                            if (registration) {
                                // if someone clicks quickly and re-adds the removed registration,
                                // the findOrCreate returns the 'removed' entry. Only delete the
                                // registration if it is still removed
                                if (registration.state == 'removed') {
                                    fencerData.value = deleteRegistration(fencerData.value, registration);
                                }
                            }
                        }, 3000);
                    }
                    else if (registration) {
                        registration.state = 'error';
                        fencerData.value = updateRegistration(fencerData.value, registration);
                    }
                })
                .catch((e) => {
                    console.log(e);
                    console.log('error on save of registration for ', pFencer.id, sideEvent?.id, roleId);
                    if (registration) {
                        registration.state = 'error';
                        fencerData.value = updateRegistration(fencerData.value, registration);
                    }
            });
        }
        else {
            console.log("ERROR: could not find registration for fencer ", pFencer, sideEvent, roleId);
        }
    }

    function forEachRegistrationDo(callback:Function)
    {
        Object.keys(fencerData.value).map((key:string) => {
            let fencer = fencerData.value[key];
            if (fencer && fencer.registrations) {
                fencer.registrations.map((reg:Registration) => callback(fencer, reg));
            }
        });
    }

    function updatePayment(registrationList:Registration[], paidHod:boolean|null, paidOrg:boolean|null, state:string)
    {
        let fencerIds:number[] = registrationList.map((reg) => reg.fencerId || 0);
        let regIds:number[] = registrationList.map((reg) => reg.id || 0);
        var newData:FencerById = {};
        Object.keys(fencerData.value).map((key:string) => {
            let fencer = fencerData.value[key];
            if (fencerIds.includes(fencer.id) && fencer.registrations) {
                fencer.registrations = fencer.registrations.map((reg:Registration) => {
                    if (regIds.includes(reg.id || 0)) {
                        if (paidOrg !== null) {
                            reg.paid = paidOrg ? 'Y': 'N';
                        }
                        if (paidHod !== null) {
                            reg.paidHod = paidHod ? 'Y' : 'N';
                        }
                        // only overwrite state if we are not re-saving it
                        if ((paidHod !== null || paidOrg !== null) || reg.state == 'saved') {
                            reg.state = state;
                        }
                    }
                    return reg;
                });
            }
            newData[key] = fencer;
        });
        fencerData.value = newData;
    }

    function markPayments(registrations:Registration[], paidHod:boolean|null, paidOrg:boolean|null)
    {
        updatePayment(registrations, paidHod, paidOrg, 'saving');
        payregistration(registrations, paidHod, paidOrg)
            .then((data) => {
                if (data && data.status == 'ok') {
                    updatePayment(registrations, paidHod, paidOrg, 'saved');
                    window.setTimeout(() => {
                        updatePayment(registrations, null, null, '');
                    }, 3000);
                }
            })
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
        setCountry, getRegistrations, addFencer, saveRegistration, removeRegistration,
        forEachRegistrationDo, markPayments
    }
})
