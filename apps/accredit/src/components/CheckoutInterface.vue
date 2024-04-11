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
import { savedocument } from '../../../common/api/accreditations/savedocument';
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
const checkinDialog = ref(false);
const pendingDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const processedDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const checkedOutDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const currentDocument:Ref<AccreditationDocument|null> = ref(null);
const currentFencer:Ref<Fencer|null> = ref(null);
const currentBadge:Ref<Code|null> = ref(null);
const currentCard:Ref<Code|null> = ref(null);
const currentDocumentCode:Ref<Code|null> = ref(null);
const markedDocumentList:Ref<Array<number>> = ref([]);

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
    data.badgeDispatcher(codeObject.original, codeObject).then((dt:Fencer|void) => {
        auth.hasLoaded('badge');
        if (dt) {
            currentFencer.value = dt;
            currentBadge.value = codeObject;

            // create a list of all documents for this fencer. If this fencer
            // is a HoD, add all documents of fencers of that country
            markedDocumentList.value = [];
            const isHod = fencerIsHod(dt, dt.countryId || 0);
            let found:AccreditationDocument|null = null;
            pendingDocumentList.value.map((doc:AccreditationDocument) => {
                if (doc.badge == codeObject.original) {
                    found = doc;
                    markedDocumentList.value.push(doc.id || 0);
                }
                else if(doc.countryId == dt.countryId && isHod) {
                    markedDocumentList.value.push(doc.id || 0);
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
                if (doc.badge == codeObject.original) {
                    found = doc;
                    markedDocumentList.value.push(doc.id || 0);
                }
                else if(doc.countryId == dt.countryId && isHod) {
                    markedDocumentList.value.push(doc.id || 0);
                }
            });

            // if the checkout dialog is already opened, do not reopen it or pop up
            // alerts. The expected process is that the bag card or document is scanned
            // first, then the badge of the recipient to check that that person is 
            // allowed to receive it.
            // The same goes for the checkin dialog: if it is open, do open another dialog
            if (!checkoutDialog.value && !checkinDialog.value) {
                // only open it if we have exactly one document pending
                if (found != null && markedDocumentList.value.length == 1) {
                    return startCheckout(found)
                }
                else if (!found) {
                    if (basic.eventCombinesCheckinCheckout()) {
                        return startCheckin();
                    }
                    else {
                        alert("You scanned a badge for which no bag is being processed. Please scan again, or reload the page");
                    }
                }
                // else we have several, pick the right one manually
            }
            if (checkinDialog.value) {
                markedDocumentList.value = []; // do not mark all pending bags when we do check-in
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
    console.log('cardDispatcher', code, codeObject);
    if (basic.eventRequiresCards()) {
        currentCard.value = codeObject;
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
            console.log('card was found, starting checkout');
            return startCheckout(found);
        }
        else if(basic.eventCombinesCheckinCheckout())
        {
            console.log('card not found, starting checkin');
            return startCheckin();
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
        currentDocumentCode.value = codeObject;
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
        else if(basic.eventCombinesCheckinCheckout()) {
            return startCheckin();
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
        if (startProcessDialog.value || checkinDialog.value) {
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
            if (basic.eventCombinesCheckinCheckout()) {
                data.subtitle = 'Weapon Control Check-in/Check-out';
            }
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
    console.log('getting country ', cid);
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
    checkinDialog.value = false;
}

function startCheckout(document:AccreditationDocument)
{
    currentDocument.value = document;
    startProcessDialog.value = false;
    checkoutDialog.value = true;
    checkinDialog.value = false;
}

function startCheckin()
{
    startProcessDialog.value = false;
    checkoutDialog.value = false;
    checkinDialog.value = true;
}

function onDialogClose()
{
    startProcessDialog.value = false;
    checkoutDialog.value = false;
    checkinDialog.value = false;
    currentFencer.value = null;
}

function onDialogSubmit(doc:AccreditationDocument)
{
    moveDocumentToList(doc);
    onDialogClose();
}

function onCheckinDialogSubmit()
{
    currentDocument.value = {id: 0};
    if (basic.eventRequiresCards() && !basic.eventAllowsIncompleteCheckin() && !currentCard.value) {
        alert("Cannot check in without a connected card");
        return false;
    }
    else if (currentCard.value) {
        currentDocument.value.card = currentCard.value.data;
    }
    if (basic.eventRequiresDocuments() && !basic.eventAllowsIncompleteCheckin() && !currentDocumentCode.value) {
        alert("Cannot check in without a connected document");
        return false;
    }
    else if (currentDocumentCode.value) {
        currentDocument.value.document = currentDocumentCode.value.data;
    }
    if (!currentBadge.value) {
        alert('Cannot check in without a connected fencer');
        return false;
    }
    else {
        currentDocument.value.badge = currentBadge.value.original;
        currentDocument.value.fencerId = currentFencer.value?.id;
    }

    auth.isLoading('savecheckindocument');
    savedocument(currentDocument.value).then((dt) => {
        auth.hasLoaded('savecheckindocument');

        if (!dt || !dt.checkin) {
            throw new Error("invalid response");
        }
        moveDocumentToList(dt);
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

    console.log('moving document', doc.processEnd, doc.checkout, foundInCheckedOut, foundInProcessed, foundInPending);
    if (doc.checkout && !foundInCheckedOut) {
        console.log('adding document to checkoutList');
        pendingDocumentList.value = pendingDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        processedDocumentList.value = processedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        checkedOutDocumentList.value.unshift(doc);
    }
    else if (doc.processEnd && !doc.checkout && !foundInProcessed) {
        console.log('adding document to processedList');
        pendingDocumentList.value = pendingDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        checkedOutDocumentList.value = checkedOutDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        processedDocumentList.value.unshift(doc);
    }
    else if (!doc.processEnd && !doc.checkout && !foundInPending) {
        console.log('adding document to pendingList');
        processedDocumentList.value = processedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        checkedOutDocumentList.value = checkedOutDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        pendingDocumentList.value.unshift(doc);
    }
    else {
        console.log('skipping moving document');
    }
}

function showInterface()
{
    return auth.isCheckout(auth.eventId, 'code') || (auth.isCheckin(auth.eventId, 'code') && basic.eventCombinesCheckinCheckout());
}

import StartProcessDialog from './StartProcessDialog.vue';
import CheckoutDialog from './CheckoutDialog.vue';
import CheckinDialog from './CheckinDialog.vue';
import { fencerIsHod } from './lib/fencerIsHod';
</script>
<template>
    <div class="main-app checkout-interface" v-if="showInterface()">
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
                        <th></th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-for="entity in pendingDocumentList" :key="entity.id" :class="{
                            'checkout-row': true,
                            'started': entity.status == 'P',
                            'pendinglist': markedDocumentList.includes(entity.id || 0)
                        }" @dblclick="() => startProcess(entity)">
                        <td class="code">{{ entity.badge }}</td>
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td class="days">{{ entity.dates?.join(', ') }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="date">{{ dayjs(entity.checkin).format('ddd D HH:mm') }}</td>
                        <td class="date" v-if="basic.eventMarksStartOfProcessing()">{{ entity.processStart ? dayjs(entity.processStart).format('ddd D HH:mm') : '' }}</td>
                        <td class="mark">&nbsp;</td>
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
                        <th></th>
                    </tr>                    
                </thead>
                <tbody>
                    <tr v-for="entity in processedDocumentList" :key="entity.id"  :class="{
                            'checkout-row': true,
                            'processed': true,
                            'error': entity.status == 'E',
                            'pendinglist': markedDocumentList.includes(entity.id || 0)
                        }" @dblclick="() => startCheckout(entity)">
                        <td class="code">{{ entity.badge }}</td>
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td class="days">{{ entity.dates?.join(', ') }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="date">{{ dayjs(entity.checkin).format('ddd D HH:mm') }}</td>
                        <td class="date">{{ dayjs(entity.processEnd).format('ddd D HH:mm') }}</td>
                        <td class="mark">&nbsp;</td>
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
                        <th></th>
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
                        <td class="mark">&nbsp;</td>
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
        <CheckinDialog
            :fencer="currentFencer"
            :card="currentCard"
            :document="currentDocumentCode"
            :visible="checkinDialog"
            @on-submit="onCheckinDialogSubmit"
            @on-cancel="onDialogClose"
        />
    </div>
</template>