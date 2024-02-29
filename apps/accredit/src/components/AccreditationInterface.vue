<script lang="ts" setup>
import type { Ref } from 'vue';
import type { Fencer, FencerById } from '../../../common/api/schemas/fencer';
import type { Registration } from '../../../common/api/schemas/registration';
import type { Code, CodeUser } from '../../../common/api/schemas/codes';
import type { StringKeyedString } from '../../../common/types';
import type { ReturnStatusSchema } from '../../../common/api/schemas/returnstatus';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
import { useDataStore } from '../stores/data';
import { registrationsstate } from '../../../common/api/registrations/registrationsstate';
import { handout } from '../../../common/api/accreditations/handout';

const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const basic = useBasicStore();
const data = useDataStore();

interface RegistrationState {
    registration: Registration;
    state:string;
}

const fencers:Ref<FencerById> = ref({});
const accreditationList:Ref<string[]> = ref([]);
const currentAccreditation:Ref<string> = ref('');
const dialogVisible = ref(false);
const updatedRegistrations:Ref<RegistrationState[]> = ref([]);
const originalStates:Ref<StringKeyedString> = ref({});

function badgeDispatcher(code:string, codeObject:Code)
{
    auth.isLoading('badge');
    data.badgeDispatcher(code, codeObject).then((dt:Fencer|void) => {
        auth.hasLoaded('badge');

        if (dt) {
            if (code != currentAccreditation.value && currentAccreditation.value.length >= 14) {
                // we scan several badges after each other. Scanning a new badge
                // confirms handing out the previous badge
                console.log('handing out the previous accreditation');
                handout(currentAccreditation.value);
                currentAccreditation.value = '';
            }

            accreditationList.value.unshift(code);
            currentAccreditation.value = code;
            updatedRegistrations.value = [];
            originalStates.value = {};

            dt.registrations = (dt.registrations || []).map((reg:Registration) => {
                originalStates.value['r' + reg.id] = reg.state || 'R';
                if (!reg.state || !['A', 'P'].includes(reg.state)) {
                    updatedRegistrations.value.push({registration: reg, state: 'R'});
                    reg.state = 'P';
                }
                return reg;
            });
            if (Object.keys(updatedRegistrations.value).length > 0) {
                // update all these registrations, mark them as 'Present', if they were marked as 'Registration-only'
                registrationsstate(updatedRegistrations.value.map((v) => v.registration), 'P', 'R');
            }

            fencers.value[code] = dt;
            dialogVisible.value = true;
        }
    })
    .catch((e) => {
        auth.hasLoaded('badge');
        console.log(e);
        alert("There was an error retrieving the accreditation information. Please try again.");
    });
}

function failDispatcher(code:string, codeObject:Code)
{
    // assume the user hit the 'enter' key
    // We assume the 'enter' key means clicking the 'Ok' button, so just close the dialog
    dialogVisible.value = false;

    // closing the dialog this way means confirming the handing-out of the badge
    console.log(currentAccreditation.value, currentAccreditation.value.length);
    if (currentAccreditation.value.length >= 14) {
        console.log('handing out the previous accreditation after closing dialog due to fail scan');
        handout(currentAccreditation.value);
        currentAccreditation.value = '';
    }
    currentAccreditation.value = '';
}

watch(() => props.visible,
    (nw) => {
        if (nw) {
            data.subtitle = "Accreditation Handout";
            data.clearDispatchers();
            data.setDispatcher('badge', badgeDispatcher);
            data.setDispatcher('fail', failDispatcher);
        }
    },
    { immediate: true }
);

function getRole(fencer?:Fencer)
{
    return basic.decorateRegistrations(fencer?.registrations || []).map((reg) => reg.role).filter((v) => v != '').join(', ');
}

function getCountry(fencer?:Fencer)
{
    let key = 'c' + fencer?.countryId;
    if (basic.countriesById[key]) {
        return basic.countriesById[key].abbr;
    }
    return '???';
}

function onDialogClose()
{
    console.log(currentAccreditation.value, currentAccreditation.value.length);
    if (currentAccreditation.value.length >= 14) {
        console.log('handing out the previous accreditation after closing dialog due to fail scan');
        handout(currentAccreditation.value);
        currentAccreditation.value = '';
    }
    dialogVisible.value = false;
}

function updatedPendingChanges(reg:Registration)
{
    // update our list of changed values
    updatedRegistrations.value = updatedRegistrations.value.map((v) => {
        if (v.registration.id == reg.id) {
            v.state = reg.state || 'R';
        }
        return v;
    });
    if (!updatedRegistrations.value.map((v) => v.registration.id).includes(reg.id)) {
        updatedRegistrations.value.push({registration: reg, state: reg.state || 'R'})
    }
}

function onDialogUpdate(field:any)
{
    auth.isLoading('update');
    registrationsstate([field.registration], field.registration.state).then(() => {
        auth.hasLoaded('update');
        let fencer = fencers.value[currentAccreditation.value];
        if (fencer && field.registration.fencerId == fencer.id) {
            fencer.registrations = (fencer.registrations || []).map((reg:Registration) => {
                if (reg.id == field.registration.id) {
                    return field.registration;
                }
                return reg;
            })
        }
        fencers.value[currentAccreditation.value] = fencer;
        updatedPendingChanges(field.registration);
    })
    .catch((e) => {
        auth.hasLoaded('update');
        console.log(e);
        alert('There was an error storing the registration state. Please reload the page and check the registration state of this person');
    })
}

function onDialogCancel()
{
    let wasUnsetIsPresent:Registration[] = [];
    let wasUnsetIsAbsent:Registration[] = [];
    let wasPresentIsUnset:Registration[] = [];
    let wasPresentIsAbsent:Registration[] = [];
    let wasAbsentIsUnset:Registration[] = [];
    let wasAbsentIsPresent:Registration[] = [];

    updatedRegistrations.value.map((v) => {
        let originalState = originalStates.value['r' + v.registration.id] || 'R';
        switch (v.state) {
            case 'A':
                if (originalState == 'R') wasUnsetIsAbsent.push(v.registration);
                if (originalState == 'P') wasPresentIsAbsent.push(v.registration);
                break;
            case 'P':
                if (originalState == 'R') wasUnsetIsPresent.push(v.registration);
                if (originalState == 'A') wasAbsentIsPresent.push(v.registration);
                break;
            default:
                // should never occur, these lists remain empty
                if (originalState == 'P') wasPresentIsUnset.push(v.registration);
                if (originalState == 'A') wasAbsentIsUnset.push(v.registration);
                break;
        }
    });

    auth.isLoading('cancel');
    let promises:Promise<ReturnStatusSchema|null>[] = [];
    if (wasPresentIsUnset.length) {
        promises.push(registrationsstate(wasPresentIsUnset, 'P', 'R'));
    }
    if (wasAbsentIsUnset.length) {
        promises.push(registrationsstate(wasAbsentIsUnset, 'A', 'R'));
    }
    if (wasUnsetIsPresent.length) {
        promises.push(registrationsstate(wasUnsetIsPresent, 'R', 'P'));
    }
    if (wasAbsentIsPresent.length) {
        promises.push(registrationsstate(wasAbsentIsPresent, 'A', 'P'));
    }
    if (wasUnsetIsAbsent.length) {
        promises.push(registrationsstate(wasUnsetIsAbsent, 'R', 'A'));
    }
    if (wasPresentIsAbsent.length) {
        promises.push(registrationsstate(wasPresentIsAbsent, 'P', 'A'));
    }
    Promise.all(promises).then(() => {
        auth.hasLoaded('cancel');
        // cancelling means not handing out the badge
        currentAccreditation.value = '';
    })
    .catch((e) => {
        console.log(e);
        alert("There was an error storing the registration state. Please reload the page and check the registration state of this person.");
        auth.hasLoaded('cancel');
    });
}

function onDialogRegister(state:string)
{
    auth.isLoading('unregister');
    registrationsstate(fencers.value[currentAccreditation.value].registrations || [], state).then(() => {
        let fencer = fencers.value[currentAccreditation.value];
        fencer.registrations = (fencer.registrations || []).map((reg:Registration) => {
            reg.state = state;
            updatedPendingChanges(reg);
            return reg;
        });
        fencers.value[currentAccreditation.value] = fencer;
        auth.hasLoaded('unregister');
    })
    .catch((e) => {
        console.log(e);
        alert("There was an error storing the registration state. Please reload the page and try again.");
        auth.hasLoaded('unregister');
    })
}

import AccreditationDialog from './AccreditationDialog.vue';
import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="main-app accreditor-interface" v-if="auth.isAccreditor(auth.eventId, 'code')">
        <div class="table-wrapper">
            <table class="processed-list style-stripes">
                <thead>
                    <tr>
                        <th>Badge</th>
                        <th colspan="2">Name</th>
                        <th>Gender</th>
                        <th>Country</th>
                        <th>Roles/Competitions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(code, i) in accreditationList" :key="i" class="accreditation-badge">
                        <td class="code">{{ code }}</td>
                        <td class="lastname">{{ fencers[code]?.lastName || '' }}</td>
                        <td class="firstname">{{ fencers[code]?.firstName || '' }}</td>
                        <td class="gender">{{ (fencers[code]?.gender || '') == 'F' ? 'Female' : 'Male' }}</td>
                        <td class="country">{{ getCountry(fencers[code]) }}</td>
                        <td class="role">{{ getRole(fencers[code]) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <AccreditationDialog
            :fencer="fencers[currentAccreditation]"
            :visible="dialogVisible"
            @on-close="onDialogClose"
            @on-update="onDialogUpdate"
            @on-cancel="onDialogCancel"
            @on-unregister="() => onDialogRegister('A')"
            @on-register="() => onDialogRegister('P')"
        />
    </div>
</template>