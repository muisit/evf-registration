<script lang="ts" setup>
import { ref, watch, computed } from 'vue';
import type { Ref } from 'vue';
import { useDataStore } from '../stores/data';
import { useAuthStore } from '../../../common/stores/auth';
import { defaultEvent } from '../../../common/api/schemas/event';
import type { Event } from '../../../common/api/schemas/event';
import type { SideEvent } from '../../../common/api/schemas/sideevent';
import type { EventRoles, EventRole } from '../../../common/api/schemas/eventroles';
import type { ValidationResult } from '../../../common/types';
import { saveevent } from '../../../common/api/event/saveevent';
import { savesides } from '../../../common/api/event/savesides';
import { saveroles } from '../../../common/api/event/saveroles';
import { eventroles } from '../../../common/api/event/roles';
const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();
const validationResult:Ref<ValidationResult> = ref({});

const currentEvent:Ref<Event> = ref(defaultEvent());
const currentSides:Ref<SideEvent[]> = ref([]);
const currentRoles:Ref<EventRoles> = ref({roles:[], users:[]});
watch(() => [props.visible, auth.userName, data.currentEvent.id],
    (nw) => {
        if (nw[0] && auth.isSysop()) {
            console.log('resetting values');
            currentEvent.value = Object.assign({}, data.currentEvent);
            currentSides.value = data.sideEvents.filter((e) => !e.competition).slice();
            eventroles().then((dt:EventRoles|null) => {
                if (dt) {
                    currentRoles.value = dt;
                }
            })
        }
});

const payments = [
    { value: 'all', text: 'Group or Individual'},
    { value: 'group', text: 'Group payments only'},
    { value: 'individual', text: 'Individual payments only'}
];

const currencies = [
    {"name":"Euro","code":"EUR","symbol":"€"},
    {"name":"British Pound","code":"GBP","symbol":"£"},
    {"name":"Swiss Franc","code":"CHF","symbol":"CHF"},
    {"name":"Danish Krone","code":"DKK","symbol":"kr."},
    {"name":"Icelandic Króna","code":"ISK","symbol":"kr."},
    {"name":"Norwegian Krone","code":"NOK","symbol":"kr."},
    {"name":"Swedish Krona","code":"SEK","symbol":"kr."},
    {"name":"Hungarian Forint","code":"HUF","symbol":"Ft"},
    {"name":"Polish Złoty","code":"PLN","symbol":"zł"},
    {"name":"Unknown","code":"UNK","symbol":"-"},
];

const eventSymbol = computed(() => {
    let retval = '€';
    currencies.map((cr) => {
        if (currentEvent.value.bank?.currency == cr.code) {
            retval = cr.symbol;
        }
    })
    return retval;
});

function configValue(label:string)
{
    switch(label) {
        case 'allow_more_teams':
            return currentEvent.value.config.allow_more_teams || false;
        case "allow_registration_lower_age":
            return currentEvent.value.config.allow_registration_lower_age || false;
        case "no_accreditations":
            return currentEvent.value.config.no_accreditations || false;
        case "use_accreditation":
            return currentEvent.value.config.use_accreditation || false;
        case "use_registration":
            return currentEvent.value.config.use_registration || false;
    }
    return false;
}

function setConfig(e:any, label:string)
{
    switch(label) {
        case 'allow_more_teams':
            currentEvent.value.config.allow_more_teams = e ? true : false;
            break;
        case "allow_registration_lower_age":
            currentEvent.value.config.allow_registration_lower_age = e ? true : false;
            break;
        case "no_accreditations":
            currentEvent.value.config.no_accreditations = e ? true : false;
            break;
        case "use_accreditation":
            currentEvent.value.config.use_accreditation = e ? true : false;
            break;
        case "use_registration":
            currentEvent.value.config.use_registration = e ? true : false;
            break;
    }
}

function saveData()
{
    validationResult.value = {};
    auth.isLoading('saveevent');
    saveevent(currentEvent.value)
        .then((e) => {
            auth.hasLoaded('saveevent');
            if (e && e.id == data.currentEvent.id) {
                data.getEvents(''+ e.id);
            }
        })
        .catch((e) => {
            auth.hasLoaded('saveevent');
            if (e.status == 422) {
                validationResult.value = e.data;
            }
            else {
                console.log(e);
                alert("There was a problem saving the data. Please reload and try again");
            }
        });
}

function saveSides()
{
    auth.isLoading('savesides');
    savesides(currentSides.value)
        .then((dt) => {
            auth.hasLoaded('savesides');
            data.getEvents(''+ currentEvent.value.id);
        })
        .catch((e) => {
            auth.hasLoaded('savesides');
            if (e.status == 422) {
                alert('Data not saved, there were validation errors');
            }
            else {
                console.log(e);
                alert('There was a problem saving the data. Please reload and try again');
            }
        })
}

function updateSide(side:SideEvent, fieldDef:any)
{
    currentSides.value = currentSides.value.map((e:SideEvent) => {
        if (e.id == side.id) {
            switch(fieldDef.field) {
                case 'title': e.title = fieldDef.value; break;
                case 'description': e.description = fieldDef.value; break;
                case 'starts': e.starts = fieldDef.value; break;
                case 'costs': e.costs = fieldDef.value; break;
            }
        }
        return e;
    });
}

const addId = ref(-1);
function addSide()
{
    currentSides.value.push({id: addId.value, title:'', abbr: '', description:'', starts: currentEvent.value.opens || '', costs: 0, competition:null, competitionId:0})
    addId.value -= 1;
}

function getErrorClass(label:string)
{
    let retval:any = { validation: true };
    if (validationResult.value['event.' + label]) {
        retval.validationerror = true;
    }
    return retval;
}

function getErrorMessages(label:string)
{
    if (validationResult.value['event.' + label]) {
        return validationResult.value['event.' + label];
    }
    return [];
}

function updateRole(role:EventRole, fieldDef:any)
{
    let val = Object.assign({}, currentRoles.value);
    val.roles = val.roles.map((r:EventRole) => {
        if (r.id == role.id) {
            switch (fieldDef.field) {
                case 'user': r.userId = fieldDef.value; break;
                case 'role': r.role = fieldDef.value; break;
            }
        }
        return r;
    }).filter((r:EventRole) => (r.userId > 0 && r.role != '') || r.id < 0);
    currentRoles.value = val;
}

const addRoleId = ref(-1);
function addRole()
{
    let val = Object.assign({}, currentRoles.value);
    val.roles.push({id:addRoleId.value, userId:0, role:''});
    currentRoles.value = val;
    addRoleId.value -= 1;
}

function saveRoles()
{
    auth.isLoading('saveroles');
    saveroles(currentRoles.value.roles)
        .then((dt) => {
            eventroles().then((dt:EventRoles|null) => {
                auth.hasLoaded('saveroles');
                if (dt) {
                    currentRoles.value = dt;
                }
            })
            .catch((e) => {
                auth.hasLoaded('saveroles');
                console.log(e);
                alert("There was an error retrieving role data");
            })
        })
        .catch((e) => {
            auth.hasLoaded('saveroles');
            if (e.status == 422) {
                alert('Data not saved, there were validation errors');
            }
            else {
                console.log(e);
                alert('There was a problem saving the data. Please reload and try again');
            }
        })
}

function generateCodes()
{
    generatecodes().then(() => {
        data.getEvents(''+ currentEvent.value.id);
    })
    .catch((e) => {
        console.log(e);
        alert("Error regenerating codes");
    })
}

import SideEventInput from '../components/event/SideEventInput.vue';
import EventRoleInput from '../components/event/EventRoleInput.vue';
import InputErrors from '../components/special/InputErrors.vue';
import { ElForm, ElFormItem, ElInput, ElInputNumber, ElSelect, ElOption, ElCheckbox, ElDatePicker, ElButton, ElTabs, ElTabPane } from 'element-plus';
import { generatecodes } from '../../../common/api/event/generatecodes';
</script>
<template>
    <div class="event-page" v-if="props.visible">
        <h3>Event Configuration</h3>
        <ElForm>
            <ElTabs type="card">
                <ElTabPane label="General">
                    <ElFormItem label="Name" :class="getErrorClass('name')">
                        <ElInput :model-value="currentEvent.name || ''" @update:model-value="(e) => currentEvent.name = e"/>
                        <InputErrors :messages="getErrorMessages('name')"/>
                    </ElFormItem>
                    <ElFormItem label="Year" :class="getErrorClass('year')">
                        <ElInputNumber :model-value="currentEvent.year || 2020" @update:model-value="(e) => currentEvent.year = e || 2020"/>
                            <InputErrors :messages="getErrorMessages('year')"/>
                    </ElFormItem>
                    <ElFormItem label="Duration" :class="getErrorClass('duration')">
                        <ElInputNumber :model-value="currentEvent.duration || 5" @update:model-value="(e) => currentEvent.duration = e || 5"/>
                            <InputErrors :messages="getErrorMessages('duration')"/>
                    </ElFormItem>
                    <ElFormItem label="Opens" :class="getErrorClass('opens')">
                        <ElDatePicker :model-value="currentEvent.opens || '2020-01-01'" @update:model-value="(e) => currentEvent.opens = e"  value-format="YYYY-MM-DD"/>
                        <InputErrors :messages="getErrorMessages('opens')"/>
                    </ElFormItem>
                    <ElFormItem label="Registration Opens" :class="getErrorClass('reg_open')">
                        <ElDatePicker :model-value="currentEvent.reg_open || '2020-01-01'" @update:model-value="(e) => currentEvent.reg_open = e"  value-format="YYYY-MM-DD"/>
                        <InputErrors :messages="getErrorMessages('reg_open')"/>
                    </ElFormItem>
                    <ElFormItem label="Registration Close" :class="getErrorClass('reg_close')">
                        <ElDatePicker :model-value="currentEvent.reg_close || '2020-01-01'" @update:model-value="(e) => currentEvent.reg_close = e"  value-format="YYYY-MM-DD"/>
                        <InputErrors :messages="getErrorMessages('reg_close')"/>
                    </ElFormItem>
                    <ElFormItem label="Website" :class="getErrorClass('web')">
                        <ElInput :model-value="currentEvent.web || ''" @update:model-value="(e) => currentEvent.web = e"/>
                        <InputErrors :messages="getErrorMessages('web')"/>
                    </ElFormItem>
                    <ElFormItem label="E-mail" :class="getErrorClass('email')">
                        <ElInput :model-value="currentEvent.email || ''" @update:model-value="(e) => currentEvent.email = e"/>
                        <InputErrors :messages="getErrorMessages('email')"/>
                    </ElFormItem>
                    <ElFormItem label="Location" :class="getErrorClass('location')">
                        <ElInput :model-value="currentEvent.location || ''" @update:model-value="(e) => currentEvent.location = e" />
                        <InputErrors :messages="getErrorMessages('location')"/>
                    </ElFormItem>
                    <ElFormItem label="Country" :class="getErrorClass('countryId')">
                        <ElSelect :model-value="currentEvent.countryId || '1'" @update:model-value="(e) => currentEvent.countryId = e">
                            <ElOption v-for="country in data.countries" :key="country.id" :value="country.id" :label="country.name"/>
                        </ElSelect>
                        <InputErrors :messages="getErrorMessages('countryId')"/>
                    </ElFormItem>
                    <ElFormItem label="Configuration" class="config">
                        <ElCheckbox :model-value="configValue('allow_registration_lower_age')" @update:model-value="(e) => setConfig(e, 'allow_registration_lower_age')" label="Allow registration in a lower age category"/>
                        <ElCheckbox :model-value="configValue('allow_more_teams')" @update:model-value="(e) => setConfig(e, 'allow_more_teams')" label="Allow registration of more than 1 team per country"/>
                        <ElCheckbox :model-value="configValue('no_accreditations')" @update:model-value="(e) => setConfig(e, 'no_accreditations')" label="Do not automatically (re)generate badges"/>
                        <ElCheckbox :model-value="configValue('use_accreditation')" @update:model-value="(e) => setConfig(e, 'use_accreditation')" label="Open the accreditation application for this event"/>
                        <ElCheckbox :model-value="configValue('use_registration')" @update:model-value="(e) => setConfig(e, 'use_registration')" label="Open the registration application for this event"/>
                    </ElFormItem>
                    <ElFormItem class="buttons">
                        <ElButton @click="saveData" type="primary">Save</ElButton>
                    </ElFormItem>
                </ElTabPane>
                <ElTabPane label="Financial">
                    <ElFormItem label="Payments" :class="getErrorClass('payments')">
                        <ElSelect :model-value="currentEvent.payments || 'group'" @update:model-value="(e) => currentEvent.payments = e">
                            <ElOption v-for="opt in payments" :key="opt.value" :value="opt.value" :label="opt.text"/>
                        </ElSelect>
                        <InputErrors :messages="getErrorMessages('payments')"/>
                    </ElFormItem>
                    <ElFormItem label="Currency" :class="getErrorClass('currency')">
                        <ElSelect :model-value="currentEvent.bank?.currency || 'EUR'" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.currency = e}">
                            <ElOption v-for="opt in currencies" :key="opt.code" :value="opt.code" :label="opt.name"/>
                        </ElSelect>
                        <InputErrors :messages="getErrorMessages('currency')"/>
                    </ElFormItem>
                    <ElFormItem label="Base Fee" :class="getErrorClass('baseFee')">
                        <div><span class='currency'>{{ eventSymbol }}</span> <ElInputNumber :precision="2" :model-value="currentEvent.bank?.baseFee || 0" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.baseFee = e || 0}" /></div>
                        <InputErrors :messages="getErrorMessages('baseFee')"/>
                    </ElFormItem>
                    <ElFormItem label="Competition Fee" :class="getErrorClass('competitionFee')">
                        <div><span class='currency'>{{ eventSymbol }}</span> <ElInputNumber :precision="2" :model-value="currentEvent.bank?.competitionFee || 0" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.competitionFee = e || 0}" /></div>
                        <InputErrors :messages="getErrorMessages('competitionFee')"/>
                    </ElFormItem>
                    <ElFormItem label="Bank Name" :class="getErrorClass('bank')">
                        <ElInput :model-value="currentEvent.bank?.bank || ''" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.bank = e}" />
                        <InputErrors :messages="getErrorMessages('bank')"/>
                    </ElFormItem>
                    <ElFormItem label="Account Holder" :class="getErrorClass('account')">
                        <ElInput :model-value="currentEvent.bank?.account || ''" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.account = e}" />
                        <InputErrors :messages="getErrorMessages('account')"/>
                    </ElFormItem>
                    <ElFormItem label="Account Address" :class="getErrorClass('address')">
                        <ElInput :model-value="currentEvent.bank?.address || ''" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.address = e}" />
                        <InputErrors :messages="getErrorMessages('address')"/>
                    </ElFormItem>
                    <ElFormItem label="IBAN" :class="getErrorClass('iban')">
                        <ElInput :model-value="currentEvent.bank?.iban || ''" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.iban = e}" />
                        <InputErrors :messages="getErrorMessages('iban')"/>
                    </ElFormItem>
                    <ElFormItem label="SWIFT" :class="getErrorClass('swift')">
                        <ElInput :model-value="currentEvent.bank?.swift || ''" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.swift = e}" />
                        <InputErrors :messages="getErrorMessages('swift')"/>
                    </ElFormItem>
                    <ElFormItem label="Reference"  :class="getErrorClass('reference')">
                        <ElInput :model-value="currentEvent.bank?.reference || ''" @update:model-value="(e) => { if (currentEvent.bank) currentEvent.bank.reference = e}" />
                        <InputErrors :messages="getErrorMessages('reference')"/>
                    </ElFormItem>
                    <ElFormItem class="buttons">
                        <ElButton @click="saveData" type="primary">Save</ElButton>
                    </ElFormItem>
                </ElTabPane>
                <ElTabPane label="Side Events">
                    <SideEventInput v-for="(side,i) in currentSides" :key="i" :event="currentEvent" :side="side" :event-symbol="eventSymbol" @on-update="(e) => updateSide(side, e)"/>
                    <SideEventInput :event="currentEvent" @on-update="(e) => addSide()"/>
                    <ElFormItem class="buttons">
                        <ElButton @click="saveSides" type="primary">Save</ElButton>
                    </ElFormItem>
                </ElTabPane>
                <ElTabPane label="Event Roles">
                    <EventRoleInput v-for="(role,i) in currentRoles.roles" :key="i" :role="role" :event="currentEvent" :users="currentRoles.users" @on-update="(e) => updateRole(role, e)"/>
                    <EventRoleInput :event="currentEvent" :users="currentRoles.users" @on-update="(e) => addRole()"/>
                    <ElFormItem class="buttons">
                        <ElButton @click="saveRoles" type="primary">Save</ElButton>
                    </ElFormItem>
                </ElTabPane>
                <ElTabPane label="Codes">
                    <ElFormItem label="Admin Code">
                        {{ currentEvent.codes && currentEvent.codes['organiser'] }}
                    </ElFormItem>
                    <ElFormItem label="Accreditation Code">
                        {{ currentEvent.codes && currentEvent.codes['accreditation'] }}
                    </ElFormItem>
                    <ElFormItem label="Check In Code">
                        {{ currentEvent.codes && currentEvent.codes['checkin'] }}
                    </ElFormItem>
                    <ElFormItem label="Check Out Code">
                        {{ currentEvent.codes && currentEvent.codes['checkout'] }}
                    </ElFormItem>
                    <ElFormItem label="DT Code">
                        {{ currentEvent.codes && currentEvent.codes['dt'] }}
                    </ElFormItem>
                    <ElFormItem class="buttons">
                        <ElButton @click="generateCodes" type="primary">Generate</ElButton>
                    </ElFormItem>
                </ElTabPane>
            </ElTabs>
        </ElForm>
    </div>
</template>