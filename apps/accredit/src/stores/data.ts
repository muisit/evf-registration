import type { Ref } from 'vue';
import type { CodeDispatcher } from './lib/codedispatcher';
import type { Code, CodeUser } from '../../../common/api/schemas/codes';
import type { Event } from '../../../common/api/schemas/event';
import type { RoleSchema, RoleById } from '../../../common/api/schemas/role';
import { ref } from 'vue';
import { defineStore } from 'pinia'
import { defaultEvent } from '../../../common/api/schemas/event';
import { basicData } from '../../../common/api/basicdata';
import { getEvent as getEventAPI } from '../../../common/api/event/getEvent';
import { checkcode } from '../../../common/api/codes/checkcode';
import { is_valid } from '../../../common/functions';
import { processCode } from './lib/processCode';
import { useAuthStore } from '../../../common/stores/auth';
import { registrations } from '../../../common/api/registrations/registrations';
import { Fencer } from '../../../common/api/schemas/fencer';

export const useDataStore = defineStore('data', () => {
    const roles:Ref<RoleSchema[]> = ref([]);
    const countryRoles:Ref<RoleSchema[]> = ref([]);
    const organisationRoles:Ref<RoleSchema[]> = ref([]);
    const officialRoles:Ref<RoleSchema[]> = ref([]);
    const rolesById:Ref<RoleById> = ref({});

    const isLoadingData:Ref<string[]> = ref([]);
    const event:Ref<Event> = ref(defaultEvent());
    const inputValue:Ref<string> = ref('');
    const processingList:Ref<Code[]> = ref([]);
    const dispatcher:Ref<CodeDispatcher> = ref({admin: adminDispatcher, badge: badgeDispatcher});
    const scannedBadge:Ref<Fencer|null> = ref(null);

    function logout()
    {
        event.value = defaultEvent();
        processingList.value = [];
        dispatcher.value = {admin: adminDispatcher, badge: badgeDispatcher};
    }

    function hasBasicData() {
        return roles.value.length > 0;
    }

    function getBasicData(cb:Function) {
        if (!hasBasicData()) {
            const authStore = useAuthStore();
            authStore.isLoading('basic');
            return basicData('roles')
                .then((data) => {
                    authStore.hasLoaded('basic');
                    fillData(data);
                    cb();
                })
                .catch((e) => {
                    authStore.hasLoaded('basic');
                    console.log(e);
                    setTimeout(() => { getBasicData(cb); }, 500);
                });
        }
        else {
            return Promise.resolve();
        }
    }

    function fillData(data:any) {
        roles.value = [];99058223000037
        officialRoles.value = [];
        organisationRoles.value = [];
        countryRoles.value = [];
        rolesById.value = {};

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

    function setDispatcher(event:string, callback:Function|null|undefined)
    {
        switch (event) {
            case 'fail': 
                if (callback) {
                    dispatcher.value.fail = callback;
                }
                else {
                    delete dispatcher.value.fail;
                }
                break;
            case 'success': 
                if (callback) {
                    dispatcher.value.success = callback;
                }
                else {
                    delete dispatcher.value.success;
                }
                break;
            case 'complete': 
                if (callback) {
                    dispatcher.value.complete = callback;
                }
                else {
                    delete dispatcher.value.complete;
                }
                break;
            case 'badge': 
                if (callback) {
                    dispatcher.value.badge = callback;
                }
                else {
                    delete dispatcher.value.badge;
                }
                break;
            case 'card': 
                if (callback) {
                    dispatcher.value.card = callback;
                }
                else {
                    delete dispatcher.value.card;
                }
                break;
            case 'document': 
                if (callback) {
                    dispatcher.value.document = callback;
                }
                else {
                    delete dispatcher.value.document;
                }
                break;
            // cannot override admin dispatcher
        }
    }

    function adminDispatcher(code:string, codeObject:Code)
    {
        // scanning an admin code always causes a functional switch
        checkcode(codeObject, "login")
            .then((dt) => {
                if (dt.status != 'ok' || dt.action != 'login') {
                    throw new Error(dt.message || 'Error while validating code');
                }
                else {
                    logout();
                    const auth = useAuthStore();
                    auth.eventId = dt.eventId;
                    auth.sendMe();
                    getEvent(dt.eventId);
                }
            })
            .catch((e) => {
                console.log(e);
                alert("There was an error with the scanned code. Perhaps it is incorrect. Please try again.");
            });
    }

    function badgeDispatcher(code:string, codeObject:Code)
    {
        // scanning an admin code always causes a functional switch
        checkcode(codeObject, "badge")
            .then((dt) => {
                if (dt.status != 'ok' || dt.action != 'badge') {
                    throw new Error(dt.message || 'Error while validating code');
                }
                else {
                    const auth = useAuthStore();
                    if (auth.eventId != dt.eventId) {
                        // scanning a badge from a different event
                        throw new Error("Error while validating code");
                    }
                    if (!dt.fencer || is_valid(dt.fencer)) {
                        throw new Error("Error while validating code");
                    }

                }
            })
            .catch((e) => {
                console.log(e);
                alert("There was an error with the scanned code. Perhaps it is incorrect. Please try again.");
            });
    }

    function isLoading(section:string)
    {
        if (!isLoadingData.value.includes(section)) {
            isLoadingData.value.push(section);
        }
    }

    function hasLoaded(section:string)
    {
        isLoadingData.value = isLoadingData.value.filter((s) => s != section);
    }

    function isCurrentlyLoading()
    {
        return isLoadingData.value.length > 0;
    }

    function getEvent(eventId:number)
    {
        console.log('loading event ', eventId);
        if (is_valid(eventId)) {
            const auth = useAuthStore();
            return getEventAPI(eventId)
                .then((dt:Event) => {
                    event.value = dt;
                    
                    auth.eventId = event.value.id || 0;
                })
                .catch((e:any) => {
                    console.log(e);
                    event.value = defaultEvent();
                    auth.eventId = 0;
                });
            }
    }

    function addCode(eventCode:string, eventKey:any)
    {
        let c = '';
        switch (eventCode) {
            case 'KeyA': 
            case 'KeyB':
            case 'KeyC':
            case 'KeyD':
            case 'KeyE':
            case 'KeyF':
            case 'KeyG':
            case 'KeyH':
            case 'KeyI':
            case 'KeyJ':
            case 'KeyK':
            case 'KeyL':
            case 'KeyM':
            case 'KeyN':
            case 'KeyO':
            case 'KeyP':
            case 'KeyQ':
            case 'KeyR':
            case 'KeyS':
            case 'KeyT':
            case 'KeyU':
            case 'KeyV':
            case 'KeyW':
            case 'KeyX':
            case 'KeyY':
            case 'KeyZ':
                c = eventKey;
                break;
            case 'Numpad0':
            case 'Digit0':
                c = '0';
                break;
            case 'Numpad1':
            case 'Digit1':
                c = '1';
                break;
            case 'Numpad2':
            case 'Digit2':
                c = '2';
                break;
            case 'Numpad3':
            case 'Digit3':
                c = '3';
                break;
            case 'Numpad4':
            case 'Digit4':
                c = '4';
                break;
            case 'Numpad5':
            case 'Digit5':
                c = '5';
                break;
            case 'Numpad6':
            case 'Digit6':
                c = '6';
                break;
            case 'Numpad7':
            case 'Digit7':
                c = '7';
                break;
            case 'Numpad8':
            case 'Digit8':
                c = '8';
                break;
            case 'Numpad9':
            case 'Digit9':
                c = '9';
                break;
            case 'NumpadEnter':
            case 'Enter':
                processFullCode(inputValue.value);
                inputValue.value = '';
                break;
            case 'NumpadDecimal':
            case 'Delete':
            case 'Backspace':
                if (inputValue.value.length > 0) {
                    inputValue.value = inputValue.value.substring(0, inputValue.value.length - 1);
                }
                break;
            default:
                console.log(eventCode);
                break;
        }
        if (c != '') {
            inputValue.value += c;
        }
    }

    function processFullCode(value:string)
    {
        processingList.value.push(processCode(value, dispatcher.value));
    }

    function getOrgRegistrations()
    {
        return registrations();
    }

    return {
        roles, officialRoles, organisationRoles, countryRoles, rolesById,
        logout, isLoading, hasLoaded, isCurrentlyLoading,
        inputValue, processingList, addCode, processFullCode,
        event, getEvent,
        getOrgRegistrations, getBasicData,
        setDispatcher, adminDispatcher, badgeDispatcher, 
        scannedBadge
    };
});
