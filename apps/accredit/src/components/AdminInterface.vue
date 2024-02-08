<script lang="ts" setup>
import type { Ref } from 'vue';
import type { Fencer, FencerById } from '../../../common/api/schemas/fencer';
import type { Registration } from '../../../common/api/schemas/registration';
import type { Code, CodeUser } from '../../../common/api/schemas/codes';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { useBasicStore } from '../../../common/stores/basic';
import { saveuser } from '../../../common/api/codes/saveuser';
import { codeusers } from '../../../common/api/codes/codeusers';

const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();
const basic = useBasicStore();

interface UserById {
    [key:string]: CodeUser;
}

const fencers:Ref<FencerById> = ref({});
const codeUsers:Ref<UserById> = ref({});
const suggestionList:Ref<FencerById> = ref({});

watch(() => props.visible,
    (nw) => {
        if (nw) {
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
    data.badgeDispatcher(code, codeObject).then((dt:Fencer|void) => {
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

onMounted(() => {
    data.subtitle = 'Administrator Page';
    data.setDispatcher('badge', badgeDispatcher);
    data.setDispatcher('fail', failDispatcher);
});

onUnmounted(() => {
    data.setDispatcher('badge', null);
    data.setDispatcher('fail', null);
});

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
            if (basic.rolesById[rid] && ['Org', 'EVF', 'FIE'].includes(basic.rolesById[rid].type)) {
                retval.push(basic.rolesById[rid].name);
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

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="admin-interface" v-if="auth.isOrganiser(auth.eventId, 'code')">
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
    </div>
</template>