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

const pendingDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const processedDocumentList:Ref<Array<AccreditationDocument>> = ref([]);
const startedDocumentList:Ref<Array<AccreditationDocument>> = ref([]);

watch(() => auth.credentials,
    (nw) => {
        if (auth.isOverview()) {
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

watch(() => props.visible,
    (nw) => {
        if (nw) {
            documents().then((dt) => {
                if (dt) {
                    pendingDocumentList.value = [];
                    processedDocumentList.value = [];
                    startedDocumentList.value = [];
                    dt.map((doc:AccreditationDocument) => {
                        if(doc.processEnd != null && !doc.checkout) {
                            processedDocumentList.value.push(doc);
                        }
                        else if (doc.processStart != null && !doc.checkout) {
                            startedDocumentList.value.push(doc);
                        }
                        else if (!doc.checkout) {
                            pendingDocumentList.value.push(doc);
                        }
                    });
                }
            });

            data.clearDispatchers();
            data.subtitle = "Check-out Overview";
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

function moveDocumentToList(doc:AccreditationDocument)
{
    let foundInPending = false;
    let foundInProcessed = false;
    let foundInStarted = false;
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
    startedDocumentList.value = startedDocumentList.value.map((d:AccreditationDocument) => {
        if (d.id == doc.id) {
            foundInStarted = true;
            return doc;
        }
        return d;
    });

    if (doc.processEnd && !doc.checkout && !foundInProcessed) {
        pendingDocumentList.value = pendingDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        startedDocumentList.value = startedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        processedDocumentList.value.unshift(doc);
    }
    else if (doc.processStart && !doc.processEnd && !doc.checkout && !foundInStarted) {
        pendingDocumentList.value = pendingDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        processedDocumentList.value = processedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        startedDocumentList.value.unshift(doc);
    }
    else if (!doc.processStart && !doc.checkout && !foundInPending) {
        processedDocumentList.value = processedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        startedDocumentList.value = startedDocumentList.value.filter((d:AccreditationDocument) => d.id != doc.id);
        pendingDocumentList.value.unshift(doc);
    }
}

function showInterface()
{
    return auth.isOverview(auth.eventId, 'code');
}
/*
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Country</th>
                        <th v-if="basic.eventRequiresCards()">Card</th>
                        <th v-if="basic.eventRequiresDocuments()">Doc</th>
                        <th>Status</th>
                    </tr>                    
                </thead>
*/
</script>
<template>
    <div class="main-app overview-interface" v-if="showInterface()">
        <div class="table-wrapper">
            <table class="processed-list">
                <tbody>
                    <tr v-for="entity in processedDocumentList" :key="entity.id"  :class="{
                            'checkout-row': true,
                            'processed': true,
                            'error': entity.status == 'E'
                        }">
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td>Finished</td>
                    </tr>
                    <tr v-for="entity in startedDocumentList" :key="entity.id" :class="{
                            'checkout-row': true,
                            'started': true
                        }">
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td>Started</td>
                    </tr>
                    <tr v-for="entity in pendingDocumentList" :key="entity.id" :class="{
                            'checkout-row': true,
                            'pending': true
                        }">
                        <td class="name">{{ entity.name }}</td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                        <td v-if="basic.eventRequiresCards()" class="code">{{ entity.card }}</td>
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td>Waiting</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>