<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { Code } from '../../../common/api/schemas/codes';
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { useBasicStore } from '../../../common/stores/basic';
import { useBroadcasterStore } from '../../../common/stores/broadcaster';
import { documents } from '../../../common/api/accreditations/documents';
import { dayjs } from 'element-plus';

const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();
const basic = useBasicStore();
const broadcaster = useBroadcasterStore();

const startProcessDialog = ref(false);
const checkoutDialog = ref(false);
const pendingDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const processedDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const checkedOutDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const currentDocument:Ref<AccreditationDocument|null> = ref(null);
const currentFencer:Ref<Fencer|null> = ref(null);
const currentBadge:Ref<Code|null> = ref(null);

watch(() => auth.credentials,
    (nw) => {
        if (auth.isCheckout()) {
            broadcaster.subscribeToCheckout((type:string, content:AccreditationDocument) => {
                moveDocumentToList(content);
            });
        }
        else {
            broadcaster.unsubscribe('checkout');
        }
    },
    { immediate: true }
);

function badgeDispatcher(code:string, codeObject:Code)
{
    auth.isLoading('badge');
    data.badgeDispatcher(code, codeObject).then((dt:Fencer|void) => {
        auth.hasLoaded('badge');
        if (dt) {
            currentFencer.value = dt;
            currentBadge.value = codeObject;

            let found:AccreditationDocument|null = null;
            pendingDocumentList.value.map((doc:AccreditationDocument) => {
                if (doc.badge == code) {
                    found = doc;
                }
            });

            // in general, the bags/documents pending processing should not be
            // identified by scanning a user badge: users are not at the location
            // However, for sake of user-interaction, if the bag is on the 
            // pending list, but the user is coming for it, we will allow
            // checkout.
            // Just fall through and see if the user may also have something on
            // the processed list. If not, the found value will keep pointing to
            // the pending list value as expected
            processedDocumentList.value.map((doc:AccreditationDocument) => {
                if (doc.badge == code) {
                    found = doc;
                }
            });

            // if the checkout dialog is already opened, do not reopen it or pop up
            // alerts. The expected process is that the bag card or document is scanned
            // first, then the badge of the recipient to check that that person is 
            // allowed to receive it.
            if (!checkoutDialog.value) {
                if (found != null) {
                    return startCheckout(found)
                }
                else {
                    alert("You scanned a badge for which no bag is being processed. Please scan again, or reload the page");
                }
            }
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
        // find the card in either the pending or the processed lists
        let found:AccreditationDocument|null = null;
        pendingDocumentList.value.map((doc:AccreditationDocument) => {
            if (doc.card && doc.card == codeObject.data) {
                found = doc;
            }
        });
        if (found != null) {
            return startProcess(found);
        }

        processedDocumentList.value.map((doc:AccreditationDocument) => {
            if (doc.card && doc.card == codeObject.data) {
                found = doc;
            }
        });
        if (found != null) {
            return startCheckout(found);
        }
        else {
            alert("You scanned a card that is not used currently. Please scan again, or reload the page");
        }
    }
    else {
        alert("You scanned a card, but these are not used for this event");
    }
}

function documentDispatcher(code:string, codeObject:Code)
{
    if (basic.eventRequiresDocuments()) {
        // find the card in either the pending or the processed lists
        let found:AccreditationDocument|null = null;
        pendingDocumentList.value.map((doc:AccreditationDocument) => {
            if (doc.document && doc.document == codeObject.data) {
                found = doc;
            }
        });
        if (found != null) {
            return startProcess(found);
        }

        processedDocumentList.value.map((doc:AccreditationDocument) => {
            if (doc.document && doc.document == codeObject.data) {
                found = doc;
            }
        });
        if (found != null) {
            return startCheckout(found);
        }
        else {
            alert("You scanned a document that is not used currently. Please scan again, or reload the page");
        }
    }
    else {
        alert("You scanned a document, but these are not used for this event");
    }
}

function failDispatcher(code:string, codeObject:Code)
{
    // prevent the dialog from being submitted when we scan a bad code
    if (isNaN(parseFloat(code))) {
        if (startProcessDialog.value) {
            onDialogClose();
        }
    }
    else {
        alert("Incorrect code scanned, please try again");
    }
}

watch(() => props.visible,
    (nw) => {
        if (nw) {
            documents().then((dt) => {
                if (dt) {
                    pendingDocumentList.value = [];
                    processedDocumentList.value = [];
                    checkedOutDocumentList.value = [];
                    dt.map((doc:AccreditationDocument) => {
                        if (doc.checkout != null) {
                            checkedOutDocumentList.value.push(doc);
                        }
                        else if(doc.processEnd != null) {
                            processedDocumentList.value.push(doc);
                        }
                        else {
                            pendingDocumentList.value.push(doc);
                        }
                    });
                }
            });

            data.clearDispatchers();
            data.subtitle = "Weapon Control Check-out";
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

function startProcess(document:AccreditationDocument)
{
    currentDocument.value = document;
    startProcessDialog.value = true;
    checkoutDialog.value = false;
}

function startCheckout(document:AccreditationDocument)
{
    currentDocument.value = document;
    startProcessDialog.value = false;
    checkoutDialog.value = true;
}

function onDialogClose()
{
    startProcessDialog.value = false;
    checkoutDialog.value = false;
}

function onDialogSubmit(doc:AccreditationDocument)
{
    moveDocumentToList(doc);
    onDialogClose();
}

function moveDocumentToList(doc:AccreditationDocument)
{
    let foundInPending = false;
    let foundInProcessed = false;
    let foundInCheckedOut = false;
    pendingDocumentList.value = pendingDocumentList.value.map((d:AccreditationDocument) => {
        if (d.id == doc.id) {
            foundInPending = true;
            return doc;
        }
        return d;
    });
    processedDocumentList.value = processedDocumentList.value.map((d:AccreditationDocument) => {
        if (d.id == doc.id) {
            foundInProcessed = true;
            return doc;
        }
        return d;
    });
    checkedOutDocumentList.value = checkedOutDocumentList.value.map((d:AccreditationDocument) => {
        if (d.id == doc.id) {
            foundInCheckedOut = true;
            return doc;
        }
        return d;
    });

    console.log(foundInPending, foundInProcessed, foundInCheckedOut, doc.processStart, doc.processEnd, doc.checkout);
    if (doc.processStart && doc.processEnd && doc.checkout && !foundInCheckedOut) {
        pendingDocumentList.value = pendingDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        processedDocumentList.value = processedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        checkedOutDocumentList.value.unshift(doc);
    }
    if (doc.processStart && doc.processEnd && !doc.checkout && !foundInProcessed) {
        pendingDocumentList.value = pendingDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        checkedOutDocumentList.value = checkedOutDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        processedDocumentList.value.unshift(doc);
    }
    if (!doc.processEnd && !doc.checkout && !foundInPending) {
        processedDocumentList.value = processedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        checkedOutDocumentList.value = checkedOutDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        pendingDocumentList.value.unshift(doc);
    }
}

import StartProcessDialog from './StartProcessDialog.vue';
import CheckoutDialog from './CheckoutDialog.vue';
</script>
<template>
    <div class="main-app checkout-interface" v-if="auth.isCheckout(auth.eventId, 'code')">
        <div class="table-wrapper">
            <table class="processed-list">
                <thead>
                    <tr>
                        <th>Badge</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Dates</th>
                        <th v-if="basic.eventRequiresCards()">Card</th>
                        <th v-if="basic.eventRequiresDocuments()">Doc</th>
                        <th>Checkin</th>
                        <th v-if="basic.eventMarksStartOfProcessing()">Start</th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-for="entity in pendingDocumentList" :key="entity.id" :class="{
                            'checkout-row': true,
                            'started': entity.status == 'P'
                        }" @dblclick="() => startProcess(entity)">
                        <td class="code">{{ entity.badge }}</td>
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td class="days">{{ entity.dates?.join(', ') }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="date">{{ dayjs(entity.checkin).format('ddd D HH:mm') }}</td>
                        <td class="date" v-if="basic.eventMarksStartOfProcessing()">{{ entity.processStart ? dayjs(entity.processStart).format('ddd D HH:mm') : '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-wrapper">
            <table class="processed-list">
                <thead>
                    <tr>
                        <th>Badge</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Dates</th>
                        <th v-if="basic.eventRequiresCards()">Card</th>
                        <th v-if="basic.eventRequiresDocuments()">Doc</th>
                        <th>Checkin</th>
                        <th>Processed</th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-for="entity in processedDocumentList" :key="entity.id"  :class="{
                            'checkout-row': true,
                            'processed': true,
                            'error': entity.status == 'E'
                        }" @dblclick="() => startCheckout(entity)">
                        <td class="code">{{ entity.badge }}</td>
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td class="days">{{ entity.dates?.join(', ') }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="date">{{ dayjs(entity.checkin).format('ddd D HH:mm') }}</td>
                        <td class="date">{{ dayjs(entity.processEnd).format('ddd D HH:mm') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-wrapper">
            <table class="processed-list">
                <thead>
                    <tr>
                        <th>Badge</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Dates</th>
                        <th v-if="basic.eventRequiresCards()">Card</th>
                        <th v-if="basic.eventRequiresDocuments()">Doc</th>
                        <th>Checkout</th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-for="entity in checkedOutDocumentList" :key="entity.id" class="checkout-row">
                        <td class="code">{{ entity.badge }}</td>
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td class="days">{{ entity.dates?.join(', ') }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="date">{{ dayjs(entity.checkout).format('ddd D HH:mm') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <StartProcessDialog
            :document="currentDocument"
            :visible="startProcessDialog"
            @on-cancel="onDialogClose"
            @on-submit="onDialogSubmit"
        />
        <CheckoutDialog
            :document="currentDocument"
            :fencer="currentFencer"
            :badge="currentBadge"
            :visible="checkoutDialog"
            @on-close="onDialogClose"
            @on-submit="onDialogSubmit"
        />
    </div>
</template>