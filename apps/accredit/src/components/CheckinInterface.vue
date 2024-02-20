<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from 'vue';
import type { Ref } from 'vue';
import type { Code } from '../../../common/api/schemas/codes';
import type { Fencer, FencerById } from '../../../common/api/schemas/fencer';
import type { StringKeyedString } from '../../../common/types';
import type { Registration } from '../../../common/api/schemas/registration';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { useBasicStore } from '../../../common/stores/basic';
import { useBroadcasterStore } from '../../../common/stores/broadcaster';
import { is_valid, parse_date } from '../../../common/functions';
import { savedocument } from '../../../common/api/accreditations/savedocument';
import { dayjs } from 'element-plus';

const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();
const basic = useBasicStore();
const broadcaster = useBroadcasterStore();

broadcaster.subscribeToCheckin((type, content) => {
    console.log(type, content);
});

interface ProcessedEntity {
    badge?: Code;
    card?: Code;
    document?: Code;
    entered?: string;
}

const fencers:Ref<FencerById> = ref({});
const processedList:Ref<ProcessedEntity[]> = ref([]);
const currentEntity:Ref<ProcessedEntity> = ref({});

function badgeDispatcher(code:string, codeObject:Code)
{
    auth.isLoading('badge');
    data.badgeDispatcher(code, codeObject).then((dt:Fencer|void) => {
        auth.hasLoaded('badge');
        if (dt) {
            fencers.value[code] = dt;
            currentEntity.value.badge = codeObject;
            dialogVisible.value = true;
        }
    })
    .catch((e) => {
        auth.hasLoaded('badge');
        console.log(e);
        alert("There was an error retrieving the accreditation information. Please try again.");
    });
}

function cardDispatcher(code:string, codeObject:Code)
{
    if (basic.eventRequiresCards()) {
        currentEntity.value.card = codeObject;
        dialogVisible.value = true;
    }
    else {
        alert("You scanned a card, but these are not used for this event");
    }
}

function documentDispatcher(code:string, codeObject:Code)
{
    if (basic.eventRequiresDocuments()) {
        currentEntity.value.document = codeObject;
        dialogVisible.value = true;
    }
    else {
        alert("You scanned a document, but these are not used for this event");
    }
}

function failDispatcher(code:string, codeObject:Code)
{
    // prevent the dialog from being submitted when we scan a bad code
    if (isNaN(parseFloat(code))) {
        onDialogSubmit();
        onDialogClose();
    }
    else {
        alert("Incorrect code scanned, please try again");
    }
}

onMounted(() => {
    data.subtitle = "Weapon Control Check-in";
    data.setDispatcher('badge', badgeDispatcher);
    data.setDispatcher('card', cardDispatcher);
    data.setDispatcher('document', documentDispatcher);
    data.setDispatcher('fail', failDispatcher);
});

onUnmounted(() => {
    data.setDispatcher('badge', null);
    data.setDispatcher('card', null);
    data.setDispatcher('document', null);
    data.setDispatcher('fail', null);
});

function getCountry(fencer?:Fencer)
{
    let key = 'c' + fencer?.countryId;
    if (basic.countriesById[key]) {
        return basic.countriesById[key].abbr;
    }
    return '';
}

function getDays(fencer?:Fencer)
{
    let retval:StringKeyedString = {};
    fencer?.registrations?.map((reg:Registration) => {
        let sideEvent = basic.sideEventsById['s' + reg.sideEventId];
        if (sideEvent) {
            if (is_valid(sideEvent.competitionId)) {
                var dt = parse_date(sideEvent.starts);
                if (dt) {
                    retval[dt.format('MM DD ddd')] = 'Y';
                }
            }
        }
    });
    // sort first on date, then convert into ddd DD
    return Object.keys(retval).sort().map((dt) => {
        var tokens = dt.split(' ');
        return tokens[2] + ' ' + parseInt(tokens[1]);
    }).join(', ');
}

function rowIncomplete(entity:ProcessedEntity)
{
    if (!entity.badge) return true;
    if (basic.eventRequiresCards() && !basic.eventAllowsIncompleteCheckin() &&  !entity.card) return true;
    if (basic.eventRequiresDocuments() && !basic.eventAllowsIncompleteCheckin() && !entity.document) return true;
    return false;
}

function combinedList()
{
    if (!currentEntity.value.badge && !currentEntity.value.card && !currentEntity.value.document) {
        return processedList.value;
    }
    return [currentEntity.value].concat(processedList.value);
}

const dialogVisible = ref(false);
function onDialogClose()
{
    dialogVisible.value = false;
}

function onDialogSubmit()
{
    let doc:AccreditationDocument = {badge:''};
    if (basic.eventRequiresCards() && !basic.eventAllowsIncompleteCheckin() && !currentEntity.value.card) {
        alert("Cannot check in without a connected card");
        return false;
    }
    else if (currentEntity.value.card) {
        doc.card = currentEntity.value.card.data;
    }
    if (basic.eventRequiresDocuments() && !basic.eventAllowsIncompleteCheckin() && !currentEntity.value.document) {
        alert("Cannot check in without a connected document");
        return false;
    }
    else if (currentEntity.value.document) {
        doc.document = currentEntity.value.document.data;
    }
    if (!currentEntity.value.badge) {
        alert('Cannot check in without a connected fencer');
        return false;
    }
    else {
        doc.badge = currentEntity.value.badge.original;
        doc.fencerId = fencers.value[currentEntity.value.badge?.original || ''].id;
    }

    auth.isLoading('savedocument');
    savedocument(doc).then((dt) => {
        auth.hasLoaded('savedocument');

        if (!dt || !dt.entered) {
            throw new Error("invalid response");
        }
        console.log("return value is ", dt);
        let lst = processedList.value.slice();
        currentEntity.value.entered = dt?.entered;
        lst.unshift(currentEntity.value);
        processedList.value = lst;
        currentEntity.value = {};
        onDialogClose();
    })
    .catch((e) => {
        auth.hasLoaded('savedocument');
        console.log(e);
        alert("There was an error processing the data. Please reload the page and try again.");
    })
}

function onDialogCancel()
{
    currentEntity.value = {};
    onDialogClose();
}

import CheckinDialog from './CheckinDialog.vue';
</script>
<template>
    <div class="main-app checkin-interface" v-if="auth.isCheckin(auth.eventId, 'code')">
        <div class="table-wrapper">
            <table class="processed-list style-stripes">
                <thead>
                    <tr>
                        <th>Badge</th>
                        <th colspan="2">Name</th>
                        <th>Country</th>
                        <th>Competitions</th>
                        <th v-if="basic.eventRequiresCards()">Card</th>
                        <th v-if="basic.eventRequiresDocuments()">Doc</th>
                        <th>Entered</th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-for="(entity, i) in combinedList()" :key="i" :class="{ 'checkin-row': true, 'pending': rowIncomplete(entity)}">
                        <td class="code">{{ entity.badge?.original }}</td>
                        <td class="lastname">{{ fencers[entity.badge?.original || '']?.lastName || '' }}</td>
                        <td class="firstname">{{ fencers[entity.badge?.original || '']?.firstName || '' }}</td>
                        <td class="country">{{ getCountry(fencers[entity.badge?.original || '']) }}</td>
                        <td class="days">{{ getDays(fencers[entity.badge?.original || ''])}}</td>
                        <td  v-if="basic.eventRequiresCards()" class="code">{{ entity.card?.data }}</td>
                        <td  v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document?.data }}</td>
                        <td class="date">{{ dayjs(entity.entered).format('ddd D HH:mm') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        {{  dialogVisible }}
        <CheckinDialog
            :fencer="fencers[currentEntity.badge?.original || ''] || null"
            :card="currentEntity.card || null"
            :document="currentEntity.document || null"
            :visible="dialogVisible"
            @on-submit="onDialogSubmit"
            @on-cancel="onDialogCancel"
        />

    </div>
</template>