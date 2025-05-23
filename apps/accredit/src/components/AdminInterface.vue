<script lang="ts" setup>
import type { Ref } from 'vue';
import type { Fencer, FencerById } from '../../../common/api/schemas/fencer';
import type { Registration } from '../../../common/api/schemas/registration';
import type { Event } from '../../../common/api/schemas/event';
import type { Code, CodeUser } from '../../../common/api/schemas/codes';
import { ref, watch, computed } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { useBasicStore } from '../../../common/stores/basic';
import { saveuser } from '../../../common/api/codes/saveuser';
import { codeusers } from '../../../common/api/codes/codeusers';
import { saveeventconfig } from '../../../common/api/event/saveeventconfig';
import { defaultEvent } from '../../../common/api/schemas/event';

const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();
const basic = useBasicStore();

interface UserById {
    [key:string]: CodeUser;
}

const activeTab = ref('users');
const fencers:Ref<FencerById> = ref({});
const codeUsers:Ref<UserById> = ref({});
const suggestionList:Ref<FencerById> = ref({});

const currentEvent:Ref<Event> = ref(defaultEvent());
watch(() => [props.visible, auth.userName, basic.event.id],
    (nw) => {
        if (nw[0] && auth.isOrganiser(basic.event.id, 'code')) {
            currentEvent.value = Object.assign({}, basic.event);
        }
    },
    {immediate: true}
);

watch(() => props.visible,
    (nw) => {
        if (nw) {
            data.subtitle = 'Administrator Page';
            data.clearDispatchers();
            data.setDispatcher('badge', badgeDispatcher);
            data.setDispatcher('fail', failDispatcher);

            auth.isLoading("accreditationuser");
            codeusers().then((dt) => {
                auth.hasLoaded("accreditationuser");
                codeUsers.value = {};
                dt.map((user:CodeUser) => {
                    let key = 'f' + user.fencerId;
                    codeUsers.value[key] = user;
                });
                matchUserRoles();
            })
            .catch((e) => {
                auth.hasLoaded('accreditationuser');
                console.log(e);
                alert("There was an error loading back-end data. Please reload the page");
            });

            auth.isLoading('orgregistrations');
            data.getOrgRegistrations().then((dt) => {
                auth.hasLoaded('orgregistrations');
                if (dt.fencers) {
                    fencers.value = {};
                    dt.fencers.map((fencer:Fencer) => {
                        fencers.value['f' + fencer.id] = fencer;
                    });
                }
                if (dt.registrations) {
                    dt.registrations.map((registration:Registration) => {
                        let fid= 'f' + registration.fencerId;
                        if (fencers.value[fid]) {
                            if (!fencers.value[fid].registrations) {
                                fencers.value[fid].registrations = [];
                            }
                            fencers.value[fid].registrations?.push(registration);
                        }
                    });
                    matchUserRoles();
                }
            })
            .catch((e) => {
                auth.hasLoaded('orgregistrations');
                console.log(e);
                alert('There was an error loading back-end data. Please reload the page');
            });
        }
    },
    { immediate: true }
);

function badgeDispatcher(code:string, codeObject:Code)
{
    data.badgeDispatcher(codeObject.original, codeObject).then((dt:Fencer|void) => {
        if (dt) {
            let key = 'f' + dt.id;
            if (fencers.value[key]) {
                suggestionList.value[key] = fencers.value[key];
            }
        }
    });
}

function failDispatcher(code:string, codeObject:Code)
{
    // check to see if the code matches fencer surnames
    suggestionList.value = {};
    code = code.toLocaleUpperCase();
    Object.keys(fencers.value).map((key:string) => {
        var fencer = fencers.value[key];
        if ( fencer.lastName.toLocaleUpperCase().includes(code)
           || fencer.firstName.toLocaleUpperCase().includes(code)
        ) {
            suggestionList.value['f' + fencer.id] = fencer;
        }
    });
}

function matchUserRoles()
{
    if (Object.keys(codeUsers.value).length && Object.keys(codeUsers.value).length) {
        Object.keys(codeUsers.value).map((key:string) => {
            let user = codeUsers.value[key];99058223000037
            let fid = 'f' + user.fencerId;
            if (fencers.value[fid]) {
                fencers.value[fid].accreditationRole = user.type;
            }
        });
    }
}

function sortFencer(a:Fencer, b:Fencer)
{
    if (a.lastName != b.lastName) {
        return a.lastName > b.lastName ? 1 : -1;
    }
    if (a.firstName != b.firstName) {
        return a.firstName > b.firstName ? 1 : -1;
    }
    if (a.dateOfBirth && b.dateOfBirth && a.dateOfBirth != b.dateOfBirth) {
        return a.dateOfBirth > b.dateOfBirth ? 1 : -1;
    }
    return a.id > b.id ? 1 : -1;
}

const fencerList = computed(() => {
    let retval:Fencer[] = [];
    Object.keys(codeUsers.value).map((fid:string) => {
        let fencer = fencers.value[fid];
        if (fencer) {
            retval.push(fencer);
        }
    });
    retval.sort(sortFencer);

    // add the suggestion list
    let suggestions:Fencer[] = [];
    Object.keys(suggestionList.value).map((fid:string) => {
        let fencer = fencers.value[fid];
        // if the fencer exists, but was not moved (yet) to the actual users list...
        if (fencer && !codeUsers.value[fid]) {
            suggestions.push(fencer);
        }
    });
    suggestions.sort(sortFencer);

    return retval.concat(suggestions);
});

function getRole(fencer:Fencer)
{
    let retval:string[] = [];
    fencer.registrations?.map((reg:Registration) => {
        if (reg.roleId) {
            let rid = 'r' + reg.roleId;
            if (basic.rolesById[rid] && ['Org', 'EVF', 'FIE'].includes(basic.rolesById[rid].type || '')) {
                retval.push(basic.rolesById[rid].name || '');
            }
        }
    });
    return retval.join(', ');
}

function getUserBadge(fencer:Fencer)
{
    if (codeUsers.value['f' + fencer.id]) {
        return codeUsers.value['f' + fencer.id].badge;
    }
    return "";
}

function setUserRole(fencer:Fencer, value:string)
{
    fencers.value['f' + fencer.id].accreditationRole = value;

    let codeuser:CodeUser = {id:0, fencerId: fencer.id, eventId: auth.eventId, type: value, badge:''};
    if (codeUsers.value['f' + fencer.id]) {
        codeuser = codeUsers.value['f' + fencer.id];
        codeuser.type = value;
    }

    auth.isLoading('saveuser');
    saveuser(codeuser).then((dt:CodeUser|null) => {
        auth.hasLoaded('saveuser');
        if (dt) {
            if (['organiser', 'accreditation', 'checkin', 'checkout', 'dt'].includes(value)) {
                codeUsers.value['f' + fencer.id] = dt;
            }
            else if (codeUsers.value['f' + fencer.id]) {
                delete codeUsers.value['f' + fencer.id];
            }
        }
    })
    .catch((e) => {
        auth.hasLoaded('saveuser');
        console.log(e);
        alert('Something went wrong while storing the data. Please try again');
    });
}


function configValue(label:string)
{
    switch(label) {
        case 'require_cards':
            return currentEvent.value.config.require_cards || false;
        case 'require_documents':
            return currentEvent.value.config.require_documents || false;
        case "allow_incomplete_checkin":
            return currentEvent.value.config.allow_incomplete_checkin || false;
        case "allow_hod_checkout":
            return currentEvent.value.config.allow_hod_checkout || false;
        case "mark_process_start":
            return currentEvent.value.config.mark_process_start || false;
        case "combine_checkin_checkout":
            return currentEvent.value.config.combine_checkin_checkout || false;
        case 'fontsize':
            return currentEvent.value.config.overviewstyle?.fontsize || '32pt';
        case 'error':
            return currentEvent.value.config.overviewstyle?.error || 'rgb(253, 108, 108)';
        case 'finished':
            return currentEvent.value.config.overviewstyle?.finished || 'rgb(19, 156, 72)';
        case 'started':
            return currentEvent.value.config.overviewstyle?.started || 'rgb(251, 233, 137)';
        case 'pending':
            return currentEvent.value.config.overviewstyle?.pending || 'rgb(4, 40, 199)';
        case 'errorText':
            return currentEvent.value.config.overviewstyle?.errorText || 'black';
        case 'finishedText':
            return currentEvent.value.config.overviewstyle?.finishedText || 'white';
        case 'startedText':
            return currentEvent.value.config.overviewstyle?.startedText || 'black';
        case 'pendingText':
            return currentEvent.value.config.overviewstyle?.pendingText || 'white';
        case 'title':
            return currentEvent.value.config.overviewstyle?.title || 'Bag Control';
        case 'titleSize':
            return currentEvent.value.config.overviewstyle?.titleSize || '20pt';
        case 'height':
            return currentEvent.value.config.overviewstyle?.titleHeight || '50px';
        case 'logo':
            return currentEvent.value.config.overviewstyle?.logo || '';
    }
    return false;
}

function setConfig(e:any, label:string)
{
    if (typeof(currentEvent.value.config.overviewstyle) != 'object') currentEvent.value.config.overviewstyle = {};
    switch(label) {
        case 'require_cards':
            currentEvent.value.config.require_cards = e ? true : false;
            break;
        case "require_documents":
            currentEvent.value.config.require_documents = e ? true : false;
            break;
        case "allow_incomplete_checkin":
            currentEvent.value.config.allow_incomplete_checkin = e ? true : false;
            break;
        case "allow_hod_checkout":
            currentEvent.value.config.allow_hod_checkout = e ? true : false;
            break;
        case "mark_process_start":
            currentEvent.value.config.mark_process_start = e ? true : false;
            break;
        case "combine_checkin_checkout":
            currentEvent.value.config.combine_checkin_checkout = e ? true : false;
            break;
        case 'fontsize':
            currentEvent.value.config.overviewstyle.fontsize = e;
            break;
        case 'error':
            currentEvent.value.config.overviewstyle.error = e;
            break;
        case 'finished':
            currentEvent.value.config.overviewstyle.finished = e;
            break;
        case 'started':
            currentEvent.value.config.overviewstyle.started = e;
            break;
        case 'pending':
            currentEvent.value.config.overviewstyle.pending = e;
            break;
        case 'errorText':
            currentEvent.value.config.overviewstyle.errorText = e;
            break;
        case 'finishedText':
            currentEvent.value.config.overviewstyle.finishedText = e;
            break;
        case 'startedText':
            currentEvent.value.config.overviewstyle.startedText = e;
            break;
        case 'pendingText':
            currentEvent.value.config.overviewstyle.pendingText = e;
            break;
        case 'title':
            currentEvent.value.config.overviewstyle.title = e;
            break;
        case 'titleSize':
            currentEvent.value.config.overviewstyle.titleSize = e;
            break;
        case 'height':
            currentEvent.value.config.overviewstyle.titleHeight = e;
            break;
        case 'logo':
            currentEvent.value.config.overviewstyle.logo = e;
            break;
    }
    console.log('config is now', currentEvent.value.config);
}

function saveConfig()
{
    console.log('saving ', currentEvent.value.config);
    auth.isLoading('saveconfig');
    saveeventconfig(currentEvent.value.config).then(() => {
        auth.hasLoaded('saveconfig');
        basic.getEvent(basic.event.id || 0);
    })
    .catch((e) => {
        auth.hasLoaded('saveconfig');
        console.log(e);
        alert("There was an error storing the configuration. Please reload the page and try again");
    })
}

import { ElSelect, ElOption, ElTabs, ElTabPane, ElForm, ElFormItem, ElCheckbox, ElButton, ElInput } from 'element-plus';
</script>
<template>
    <div class="main-app admin-interface" v-if="auth.isOrganiser(auth.eventId, 'code')">
        <ElTabs v-model="activeTab">
            <ElTabPane label="Users" name="users">
                <table class="fencer-list">
                    <tbody>
                        <tr v-for="fencer in fencerList" :key="fencer.id" class="accreditation-user">
                            <td class="lastname">{{ fencer.lastName}}</td>
                            <td class="firstname">{{ fencer.firstName}}</td>
                            <td class="role">{{ getRole(fencer) }}</td>
                            <td>
                                <ElSelect :model-value="fencer.accreditationRole || 'none'" @update:model-value="(e) => setUserRole(fencer, e)">
                                    <ElOption value="none" label="None" />
                                    <ElOption value="organiser" label="Admin"/>
                                    <ElOption value="accreditation" label="Accreditation Hand-out"/>
                                    <ElOption value="checkin" label="Weapon Check-in"/>
                                    <ElOption value="checkout" label="Weapon Check-out"/>
                                    <ElOption value="dt" label="DT"/>
                                </ElSelect>
                            </td>
                            <td>
                                {{ getUserBadge(fencer) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </ElTabPane>
            <ElTabPane label="Configuration" name="config">
                <ElFormItem label="Cards" class="config">
                    <ElCheckbox :model-value="configValue('require_cards')" @update:model-value="(e) => setConfig(e, 'require_cards')" label="Require scanning a card during check-in"/>
                </ElFormItem>
                <ElFormItem label="Documents" class="config">
                    <ElCheckbox :model-value="configValue('require_documents')" @update:model-value="(e) => setConfig(e, 'require_documents')" label="Require scanning documents during check-in"/>
                </ElFormItem>
                <ElFormItem label="Incomplete" class="config">
                    <ElCheckbox :model-value="configValue('allow_incomplete_checkin')" @update:model-value="(e) => setConfig(e, 'allow_incomplete_checkin')" label="Allow check-in with missing card or document"/>
                </ElFormItem>
                <ElFormItem label="HoD" class="config">
                    <ElCheckbox :model-value="configValue('allow_hod_checkout')" @update:model-value="(e) => setConfig(e, 'allow_hod_checkout')" label="Allow check-out by the Head of Delegation"/>
                </ElFormItem>
                <ElFormItem label="Process" class="config">
                    <ElCheckbox :model-value="configValue('mark_process_start')" @update:model-value="(e) => setConfig(e, 'mark_process_start')" label="Mark start of the weapons check process"/>
                </ElFormItem>
                <ElFormItem label="Checkout" class="config">
                    <ElCheckbox :model-value="configValue('combine_checkin_checkout')" @update:model-value="(e) => setConfig(e, 'combine_checkin_checkout')" label="Combine Check-In and Check-Out Stations"/>
                </ElFormItem>
                <br/>
                <br/>
                <ElFormItem label="Fontsize" class="config">
                    <ElInput :model-value="configValue('fontsize')" @update:model-value="(e) => setConfig(e, 'fontsize')"/>
                </ElFormItem>
                <ElFormItem label="Pending colour" class="config">
                    <ElInput :model-value="configValue('pending')" @update:model-value="(e) => setConfig(e, 'pending')"/>
                </ElFormItem>
                <ElFormItem label="Pending text" class="config">
                    <ElInput :model-value="configValue('pendingText')" @update:model-value="(e) => setConfig(e, 'pendingText')"/>
                </ElFormItem>
                <ElFormItem label="Started colour" class="config">
                    <ElInput :model-value="configValue('started')" @update:model-value="(e) => setConfig(e, 'started')"/>
                </ElFormItem>
                <ElFormItem label="Started text" class="config">
                    <ElInput :model-value="configValue('startedText')" @update:model-value="(e) => setConfig(e, 'startedText')"/>
                </ElFormItem>
                <ElFormItem label="Finished colour" class="config">
                    <ElInput :model-value="configValue('finished')" @update:model-value="(e) => setConfig(e, 'finished')"/>
                </ElFormItem>
                <ElFormItem label="Finished text" class="config">
                    <ElInput :model-value="configValue('finishedText')" @update:model-value="(e) => setConfig(e, 'finishedText')"/>
                </ElFormItem>
                <ElFormItem label="Error colour" class="config">
                    <ElInput :model-value="configValue('error')" @update:model-value="(e) => setConfig(e, 'error')"/>
                </ElFormItem>
                <ElFormItem label="Error text" class="config">
                    <ElInput :model-value="configValue('errorText')" @update:model-value="(e) => setConfig(e, 'errorText')"/>
                </ElFormItem>
                <ElFormItem label="Overview Title" class="config">
                    <ElInput :model-value="configValue('title')" @update:model-value="(e) => setConfig(e, 'title')"/>
                </ElFormItem>
                <ElFormItem label="Overview Title Font Size" class="config">
                    <ElInput :model-value="configValue('titleSize')" @update:model-value="(e) => setConfig(e, 'titleSize')"/>
                </ElFormItem>
                <ElFormItem label="Overview height" class="config">
                    <ElInput :model-value="configValue('height')" @update:model-value="(e) => setConfig(e, 'height')"/>
                </ElFormItem>
                <ElFormItem label="Overview Logo" class="config">
                    <ElInput :model-value="configValue('logo')" @update:model-value="(e) => setConfig(e, 'logo')"/>
                </ElFormItem>
                
                <ElFormItem class="buttons">
                    <ElButton @click="saveConfig" type="primary">Save</ElButton>
                </ElFormItem>
                </ElTabPane>
        </ElTabs>
    </div>
</template>