import type { Ref } from 'vue';
import type { Event } from '../api/schemas/event';
import type { RoleSchema, RoleById } from '../api/schemas/role';
import type { CategorySchema, CategoryById } from '../api/schemas/category';
import type { CountrySchema, CountryById } from '../api/schemas/country';
import type { WeaponSchema, WeaponById } from '../api/schemas/weapon';
import type { Competition, CompetitionById } from '../api/schemas/competition';
import type { SideEvent, SideEventById } from '../api/schemas/sideevent';
import type { Registration } from '../api/schemas/registration';
import type { BasicDataSchema } from '../api/schemas/basicdata';
import { ref } from 'vue';
import { defineStore } from 'pinia'
import { defaultEvent } from '../api/schemas/event';
import { basicData } from '../api/basicdata';
import { getEvent as getEventAPI } from '../api/event/getEvent';
import { is_valid } from '../functions';
import { useAuthStore } from './auth';
import { abbreviateSideEvent } from './lib/abbreviateSideEvent';

export const useBasicStore = defineStore('basic', () => {
    const categories:Ref<CategorySchema[]> = ref([]);
    const categoriesById:Ref<CategoryById> = ref({});
    const weapons:Ref<WeaponSchema[]> = ref([]);
    const weaponsById:Ref<WeaponById> = ref({});
    const countries:Ref<CountrySchema[]> = ref([]);
    const countriesById:Ref<CountryById> = ref({});
    const roles:Ref<RoleSchema[]> = ref([]);
    const countryRoles:Ref<RoleSchema[]> = ref([]);
    const organisationRoles:Ref<RoleSchema[]> = ref([]);
    const officialRoles:Ref<RoleSchema[]> = ref([]);
    const rolesById:Ref<RoleById> = ref({});

    const event:Ref<Event> = ref(defaultEvent());
    const competitions:Ref<Competition[]> = ref([]);
    const competitionsById:Ref<CompetitionById> = ref({});
    const sideEvents:Ref<SideEvent[]> = ref([]);
    const sideEventsById:Ref<SideEventById> = ref({});
    const competitionEvents:Ref<SideEvent[]> = ref([]);
    const nonCompetitionEvents:Ref<SideEvent[]> = ref([]);

    function hasBasicData() {
        return roles.value.length > 0;
    }

    function getBasicData(): Promise<BasicDataSchema|void> {
        if (!hasBasicData()) {
            const authStore = useAuthStore();
            authStore.isLoading('basic');
            return basicData()
                .then((data) => {
                    authStore.hasLoaded('basic');
                    return fillData(data);
                })
                .catch((e) => {
                    authStore.hasLoaded('basic');
                    console.log(e);
                    return new Promise<BasicDataSchema|void>((res) => setTimeout(() => res(getBasicData()), 500));
                });
        }
        else {
            return Promise.resolve({});
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

    function getEvent(eventId:number)
    {
        if (is_valid(eventId)) {
            const auth = useAuthStore();
            return getEventAPI(eventId)
                .then((dt:Event) => {
                    setEvent(dt);
                })
                .catch((e:any) => {
                    console.log(e);
                    event.value = defaultEvent();
                    auth.eventId = 0;
                });
            }
    }

    function setEvent(pEvent?:Event) {
        competitions.value = [];
        competitionsById.value = {};
        competitionEvents.value = [];
        nonCompetitionEvents.value = [];
        sideEvents.value = [];
        sideEventsById.value = {};

        const authStore = useAuthStore();
        if (!pEvent || !is_valid(pEvent)) {
            event.value = defaultEvent();
            authStore.eventId = 0;
            return;
        }

        event.value = pEvent;
        competitions.value = [];
        competitionsById.value = {};
        competitionEvents.value = [];
        nonCompetitionEvents.value = [];
        sideEvents.value = [];
        sideEventsById.value = {};

        if (pEvent.competitions) {
            competitions.value = pEvent.competitions.map((comp:Competition) => {
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
        if (pEvent.sideEvents) {
            pEvent.sideEvents.map((se:SideEvent) => {
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
        nonCompetitionEvents.value = ses.sort((a, b) => {
            if (a.starts == b.starts) {
                return a.title > b.title ? 1 : -1;
            }
            return a.starts > b.starts ? 1 : -1;
        });
        sideEvents.value = comps.concat(ses);
        sideEvents.value.forEach((data) => sideEventsById.value['s' + data.id] = data);
        authStore.eventId = pEvent.id || 0;
    }

    function decorateRegistrations(registrations:Registration[])
    {
        return registrations.map((reg:Registration) => {
            if (!is_valid(reg.roleId)) {
                let se = sideEventsById.value['s' + reg.sideEventId];
                if (se) {
                    reg.role = se.abbr || '';
                }
            }
            else {
                let role = rolesById.value['r' + reg.roleId];
                if (role) {
                    reg.role = role.name || '';
                }
            }
            return reg;
        });
    }

    function eventRequiresCards()
    {
        if (event.value.config && event.value.config.require_cards) return true;
        return false;
    }

    function eventRequiresDocuments()
    {
        if (event.value.config && event.value.config.require_documents) return true;
        return false;
    }

    function eventAllowsIncompleteCheckin()
    {
        if (event.value.config && event.value.config.allow_incomplete_checkin) return true;
        return false;
    }
    
    function eventAllowsCheckoutByHod()
    {
        if (event.value.config && event.value.config.allow_hod_checkout) return true;
        return false;
    }

    function eventMarksStartOfProcessing()
    {
        if (event.value.config && event.value.config.mark_process_start) return true;
        return false;
    }

    function eventCombinesCheckinCheckout()
    {
        if (event.value.config && event.value.config.combine_checkin_checkout) return true;
        return false;
    }

    return {
        roles, officialRoles, organisationRoles, countryRoles, rolesById, 
        countries, countriesById, categories, categoriesById, weapons, weaponsById,
        getBasicData,
        event, competitions, competitionsById, sideEvents, sideEventsById,
        competitionEvents, nonCompetitionEvents, getEvent, setEvent,
        decorateRegistrations,
        eventRequiresCards, eventRequiresDocuments, eventAllowsIncompleteCheckin, eventAllowsCheckoutByHod,
        eventMarksStartOfProcessing, eventCombinesCheckinCheckout
    };
});
