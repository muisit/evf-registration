<script lang="ts" setup>
import { ref, watch } from 'vue';
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

interface ProcessedEntity {
    badge?: Code;
    card?: Code;
    document?: Code;
    entered?: string;
}

const fencers:Ref<FencerById> = ref({});
const processedList:Ref<AccreditationDocument[]> = ref([]);
const currentEntity:Ref<ProcessedEntity> = ref({});

watch(() => auth.credentials,
    (nw) => {
        if (auth.isCheckin()) {
            broadcaster.subscribeToCheckin((type:string, content:AccreditationDocument) => {
                if (props.visible) {
                    switch (type) {
                        case 'CheckinEvent':
                            matchDocument('checkin', content);
                            break;
                        case 'CheckoutEvent':
                            matchDocument('checkout', content);
                            break;
                        case 'ProcessStartEvent':
                            matchDocument('start', content);
                            break;
                        case 'ProcessEndEvent':
                            matchDocument('end', content);
                            break;
                    }
                }
            });
        }
        else {
            broadcaster.unsubscribe('checkin');
        }
    },
    { immediate: true }
);

function matchDocument(event:string, doc:AccreditationDocument)
{
    if (event == 'checkout') {
        processedList.value = processedList.value.filter((d:AccreditationDocument) => d.id != doc.id);
    }
    else {
        let found = false;
        processedList.value = processedList.value.map((d:AccreditationDocument) => {
            if (d.id == doc.id) {
                found = true;
                return doc;
            }
            return d;
        });
        if (!found) {
            processedList.value.unshift(doc);
        }
    }
}


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

watch(() => props.visible,
    (nw) => {
        if (nw) {
            data.clearDispatchers();
            data.subtitle = "Weapon Control Check-in";
            data.setDispatcher('badge', badgeDispatcher);
            data.setDispatcher('card', cardDispatcher);
            data.setDispatcher('document', documentDispatcher);
            data.setDispatcher('fail', failDispatcher);
        }
    },
    { immediate: true }
)

function getCountry(cid:number)
{
    let key = 'c' + cid;
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

const dialogVisible = ref(false);
function onDialogClose()
{
    dialogVisible.value = false;
}

function onDialogSubmit()
{
    let doc:AccreditationDocument = {id: 0};
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

        if (!dt || !dt.checkin) {
            throw new Error("invalid response");
        }
        processedList.value.unshift(dt);
        currentEntity.value = {};
        onDialogClose();
    })
    .catch((e) => {
        auth.hasLoaded('savedocument');
        if (e.status == 422 && e.data) {
            // validation error
            let txt='';
            if (e.data.card) {
                txt += e.data.card.join('; ') + '\r\n';
            }
            if (e.data.document) {
                txt += e.data.document.join('; ') + '\r\n';
            }
            if (e.data.badge) {
                txt += e.data.badge.join('; ') + '\r\n';
            }
            if (txt.length > 0) {
                alert(txt);
            }
            else {
                console.log(e);
                alert("There was an error processing the data. Please reload the page and try again.");
            }
        }
        else {
            console.log(e);
            alert("There was an error processing the data. Please reload the page and try again.");
        }
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
            <table class="processed-list">
                <thead>
                    <tr>
                        <th>Badge</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Competitions</th>
                        <th v-if="basic.eventRequiresCards()">Card</th>
                        <th v-if="basic.eventRequiresDocuments()">Doc</th>
                        <th>Entered</th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-if="currentEntity.badge || currentEntity.card || currentEntity.document" :class="{
                            'checkin-row': true,
                            'pending': true
                        }">
                        <td class="code">{{ currentEntity.badge?.original }}</td>
                        <td class="name">{{ fencers[currentEntity.badge?.original || '']?.lastName || '' }}, {{ fencers[currentEntity.badge?.original || '']?.firstName || '' }}</td>
                        <td class="country">{{ getCountry(fencers[currentEntity.badge?.original || '']?.countryId || 0) }}</td>
                        <td class="days">{{ getDays(fencers[currentEntity.badge?.original || ''])}}</td>
                        <td  v-if="basic.eventRequiresCards()" class="code">{{ currentEntity.card?.data }}</td>
                        <td  v-if="basic.eventRequiresDocuments()" class="code">{{ currentEntity.document?.data }}</td>
                        <td class="date">{{ dayjs(currentEntity.entered).format('ddd D HH:mm') }}</td>
                    </tr>
                    <tr v-for="doc in processedList" :key="doc.id" :class="{
                            'checkin-row': true,
                            'started': doc.processStart && !doc.processEnd,
                            'processed': doc.processEnd && !doc.checkout
                        }">
                        <td class="code">{{ doc.badge }}</td>
                        <td class="name">{{ doc.name }}</td>
                        <td class="country">{{ basic.countriesById['c' + doc.countryId].abbr }}</td>
                        <td class="days">{{ doc.dates?.join(', ') }}</td>
                        <td  v-if="basic.eventRequiresCards()" class="code">{{ doc.card}}</td>
                        <td  v-if="basic.eventRequiresDocuments()" class="code">{{ doc.document }}</td>
                        <td class="date">{{ dayjs(doc.checkin).format('ddd D HH:mm') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
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