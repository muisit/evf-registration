import { Ref, ref } from 'vue'
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
import { Event, defaultEvent } from '../../../common/api/schemas/event';
import { SideEvent } from '../../../common/api/schemas/sideevent';
import { Fencer } from '../../../common/api/schemas/fencer';
import { Competition } from '../../../common/api/schemas/competition';
import { CountrySchema } from '../../../common/api/schemas/country';
import { Registration } from '../../../common/api/schemas/registration';
import { useAuthStore } from '../../../common/stores/auth';
import { payregistration } from '../../../common/api/registrations/payregistration';

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

    const currentEvent:Ref<Event> = ref(defaultEvent());
    const events = ref([]);
    const competitions = ref([]);
    const competitionsById = ref({});
    const sideEvents = ref([]);
    const competitionEvents = ref([]);
    const nonCompetitionEvents = ref([]);
    const sideEventsById = ref({});

    const overviewData = ref([]);
    const overviewPerCountry = ref([]);

    const currentCountry:Ref<CountrySchema> = ref({id: 0, name: 'Organisation', abbr: 'Org', path: ''});
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
        if (!is_valid(eventId)) return;

        var eventFound = { id: -1, title: 'Please wait while loading', competitions: [], sideEvents: []};
        events.value.forEach((data) => {
            if (data.id == eventId) {
                eventFound = data;
            }
        });

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

        // sort side events in competition based events and side events
        var comps = [];
        var ses = [];
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
        competitionEvents.value = comps;
        nonCompetitionEvents.value = ses;
        sideEvents.value = comps.concat(ses);
        sideEventsById.value = {};
        sideEvents.value.forEach((data) => sideEventsById.value['s' + data.id] = data);

        overviewData.value = [];
        fencerData.value = {};

        const authStore = useAuthStore();
        authStore.eventId = currentEvent.value.id;
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

    function addFencer(fencer:Fencer)
    {
        console.log('inserting fencer', fencer.fullName);
        let fid = 'f' + fencer.id;
        if (fencerData.value[fid]) {
            console.log('fencer in list ', fencer.fullName, ' has ', fencerData.value[fid].registrations?.length,'registrations');
        }
        else {
            console.log('fencer not found in list before insert');
        }
        insertFencer(fencer);
        if (fencerData.value[fid]) {
            console.log('fencer in list ', fencer.fullName, ' now has ', fencerData.value[fid].registrations?.length,'registrations');
        }
        else {
            console.log('fencer not found in list after insert');
        }
    }

    function saveRegistration(pFencer:Fencer, sideEvent:SideEvent|null, roleId:string|null, teamName:string|null, payment:string|null)
    {
        console.log('saving registration for ', pFencer.id, sideEvent?.id, roleId);
        let registration = updateRegistration(pFencer, sideEvent, roleId, teamName, payment, 'saving');
        if (registration) {
            saveregistration(registration).then((data) => {
                    console.log('saved registration for ', pFencer.id, sideEvent?.id, roleId);
                    if (data && is_valid(data.id)) {
                        updateRegistration(pFencer, sideEvent, roleId, data.team, data.payment, 'saved', data.id);
                        window.setTimeout(() => {
                            console.log('clearing state of registration for ', pFencer.id, sideEvent?.id, roleId);
                            // do not overwrite teamName or payment 
                            updateRegistration(pFencer, sideEvent, roleId, null, null, '');
                        }, 3000);
                    }
                    else {
                        console.log('error on save of registration for ', pFencer.id, sideEvent?.id, roleId);
                        updateRegistration(pFencer, sideEvent, roleId, teamName, payment, 'error');    
                    }
                })
                .catch((e) => {
                    console.log(e);
                    console.log('error on save of registration for ', pFencer.id, sideEvent?.id, roleId);
                    updateRegistration(pFencer, sideEvent, roleId, teamName, payment, 'error');
                });
        }
    }

    function updateRegistration(pFencer:Fencer, sideEvent:SideEvent|null, roleId:string|null, teamName:string|null, payment:string|null, state:string|null, id:number|null = null)
    {
        let found:Registration|null = null;
        let newData = {};
        Object.keys(fencerData.value).map((key:string) => {
            let fencer = fencerData.value[key];
            if (fencer.id == pFencer.id) {
                fencer.registrations.map((registration) => {
                    if (   (roleId != null|| sideEvent != null) // either one is set, both allowed
                        && (sideEvent === null || registration.sideEventId == sideEvent.id) // unset or it matches
                        && (roleId === null || registration.roleId == roleId) // unset or it matches
                    ) {
                        if (teamName !== null) {
                            // we use '' as a replacement for 'null, yes, really null' instead of 'null, do not replace'
                            if (teamName == '') {
                                console.log('setting team name to null explicitely');
                                registration.team = null;
                            }
                            else {
                                console.log('replacing teamname with ', teamName);
                                registration.team = teamName;
                            }
                        }
                        registration.sideEventId = sideEvent ? sideEvent.id : null;
                        registration.roleId = is_valid(roleId) ? parseInt(roleId || '') : null;
                        if (payment !== null) {
                            registration.payment = payment;
                        }
                        if (id !== null) {
                            registration.id = id;
                        }
                        // only overwrite if we are not re-saving the entry
                        if (payment != null || registration.state == 'saved') {
                            registration.state = state;
                        }
                        found = registration;
                    }
                });

                if (!found) {
                    console.log('not found, creating new entry', state, sideEvent?.id, roleId);
                    found = {
                        id: id || 0,
                        fencerId: pFencer.id,
                        roleId: is_valid(roleId) ? parseInt(roleId || '0') : null,
                        sideEventId: sideEvent ? sideEvent.id : null,
                        dateTime: null,
                        payment: payment,
                        paid: null,
                        paidHod: null,
                        team: teamName,
                        state: state
                    };
                    fencer.registrations.push(found);
                }
            }
            newData['f' + fencer.id] = fencer;
        });
        fencerData.value = newData;
        return found;
    }

    function deleteRegistration(pFencer:Fencer, sideEvent:SideEvent|null, roleId:string|null)
    {
        let found:Registration|null = null;
        Object.keys(fencerData.value).map((key:string) => {
            let fencer = fencerData.value[key];
            if (fencer.id == pFencer.id) {
                fencer.registrations.map((registration) => {
                    if (sideEvent && registration.sideEventId == sideEvent.id) {
                        found = registration;
                    }
                    else if (!sideEvent && !registration.sideEventId && registration.roleId == parseInt(roleId || '')) {
                        found = registration;
                    }
                });
            }
        });
        if (found) {
            let regId = found?.id || 0;
            updateRegistration(pFencer, sideEvent, roleId, found.team ? '' : null, null, 'saving', 0); // set the id to 0
            deleteregistration(regId)
                .then((data) => {
                    if (data && data.status == 'ok') {
                        updateRegistration(pFencer, sideEvent, roleId, null, null, 'saved');
                        window.setTimeout(() => {
                            updateRegistration(pFencer, sideEvent, roleId, null,  null, '');
                            filterOutRegistration(pFencer, sideEvent, roleId);
                        }, 3000);
                    }
                    else {
                        updateRegistration(pFencer, sideEvent, roleId, null, null, 'error');    
                    }
                })
                .catch((e) => {
                    console.log(e);
                    console.log('error on save of registration for ', pFencer.id, sideEvent?.id, roleId);
                    updateRegistration(pFencer, sideEvent, roleId, null, null, 'error');
                });
        }
        else {
            console.log("ERROR: could not find registration for fencer ", pFencer, sideEvent, roleId);
        }
    }

    function filterOutRegistration(pFencer:Fencer, sideEvent:SideEvent|null, roleId:string|null)
    {
        Object.keys(fencerData.value).map((key:string) => {
            let fencer = fencerData.value[key];
            if (fencer.id == pFencer.id) {
                fencer.registrations = fencer.registrations.filter((registration) => {
                    // if still marked for deleting (id == 0)
                    if (registration.id == 0) {
                        if (sideEvent && registration.sideEventId == sideEvent.id) {
                            return false;
                        }
                        else if (!sideEvent && !registration.sideEventId && registration.roleId == parseInt(roleId || '')) {
                            return false;
                        }
                    }
                    return true;
                });
                fencerData.value['f' + fencer.id] = fencer;
            }
        });
    }

    function forEachRegistrationDo(callback)
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
        let fencerIds = registrationList.map((reg) => reg.fencerId);
        let regIds = registrationList.map((reg) => reg.id);
        var newData = {};
        Object.keys(fencerData.value).map((key:string) => {
            let fencer = fencerData.value[key];
            if (fencerIds.includes(fencer.id) && fencer.registrations) {
                fencer.registrations = fencer.registrations.map((reg:Registration) => {
                    if (regIds.includes(reg.id)) {
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
        setCountry, getRegistrations, addFencer, saveRegistration, deleteRegistration,
        forEachRegistrationDo, markPayments
    }
})
