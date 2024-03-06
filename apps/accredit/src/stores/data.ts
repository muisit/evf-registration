import type { Ref } from 'vue';
import type { CodeDispatcher } from './lib/codedispatcher';
import type { Code, CodeUser } from '../../../common/api/schemas/codes';
import type { Event } from '../../../common/api/schemas/event';
import type { Fencer } from '../../../common/api/schemas/fencer';
import { ref } from 'vue';
import { defineStore } from 'pinia'
import { checkcode } from '../../../common/api/codes/checkcode';
import { is_valid } from '../../../common/functions';
import { processCode } from './lib/processCode';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
import { registrations } from '../../../common/api/registrations/registrations';

export const useDataStore = defineStore('data', () => {
    const subtitle:Ref<string> = ref('');
    const inputValue:Ref<string> = ref('');
    const processingList:Ref<Code[]> = ref([]);
    const dispatcher:Ref<CodeDispatcher> = ref({admin: adminDispatcher, badge: badgeDispatcher});
    const scannedBadge:Ref<Fencer|null> = ref(null);

    function logout()
    {
        const basicStore = useBasicStore();
        basicStore.setEvent();
        processingList.value = [];
        dispatcher.value = {admin: adminDispatcher, badge: badgeDispatcher};
    }

    function setDispatcher(event:string, callback:Function|null|undefined)
    {
        console.log('setting dispatcher for ', event);
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

    function clearDispatchers()
    {
        delete dispatcher.value.success;
        delete dispatcher.value.fail;
        delete dispatcher.value.complete;
        delete dispatcher.value.badge;
        delete dispatcher.value.card;
        delete dispatcher.value.document;
    }

    function adminDispatcher(code:string, codeObject:Code)
    {
        // scanning an admin code always causes a functional switch
        return checkcode(codeObject, "login")
            .then((dt) => {
                if (dt.status != 'ok' || dt.action != 'login') {
                    throw new Error(dt.message || 'Error while validating code');
                }
                else {
                    logout();
                    const auth = useAuthStore();
                    auth.eventId = dt.eventId;
                    auth.sendMe();

                    const basicStore = useBasicStore();
                    basicStore.getEvent(dt.eventId);
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
        return checkcode(codeObject, "badge")
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
                    if (!dt.fencer || !is_valid(dt.fencer)) {
                        throw new Error("Error while validating code");
                    }
                    scannedBadge.value = dt.fencer;
                    return dt.fencer;
                }
            })
            .catch((e) => {
                console.log(e);
                alert("There was an error with the scanned code. Perhaps it is incorrect. Please try again.");
            });
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
        let code = processCode(value, dispatcher.value);
        if (code.original.length > 0) {
            processingList.value.push(code);
        }
    }

    function getOrgRegistrations()
    {
        return registrations();
    }

    return {
        subtitle,
        logout,
        inputValue, processingList, addCode, processFullCode,
        getOrgRegistrations, 
        setDispatcher, clearDispatchers, adminDispatcher, badgeDispatcher, 
        scannedBadge
    };
});
