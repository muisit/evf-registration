<script lang="ts" setup>
import { ref, watch, computed } from 'vue';
import type { Ref, StyleValue } from 'vue';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { useBasicStore } from '../../../common/stores/basic';
import { useBroadcasterStore } from '../../../common/stores/broadcaster';
import { documents } from '../../../common/api/accreditations/documents';

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

const pendingObject:Ref<StyleValue> = computed(() => {
    let baseObject:StyleValue = {
        fontSize: basic.event.config.overviewstyle?.fontsize || '32pt',
        color: basic.event.config.overviewstyle?.pendingText || 'white',
        backgroundColor: basic.event.config.overviewstyle?.pending || 'rgb(4, 40, 199)',
    };
    return baseObject;
});
const startedObject:Ref<StyleValue> = computed(() => {
    let baseObject:StyleValue = {
        fontSize: basic.event.config.overviewstyle?.fontsize || '32pt',
        color: basic.event.config.overviewstyle?.startedText || 'black',
        backgroundColor: basic.event.config.overviewstyle?.started || 'rgb(251, 233, 137)',
    };
    return baseObject;
});
const finishedObject:Ref<StyleValue> = computed(() => {
    let baseObject:StyleValue = {
        fontSize: basic.event.config.overviewstyle?.fontsize || '32pt',
        color: basic.event.config.overviewstyle?.finishedText || 'white',
        backgroundColor: basic.event.config.overviewstyle?.finished || 'rgb(19, 156, 72)',
    };
    return baseObject;
});
const errorObject:Ref<StyleValue> = computed(() => {
    let baseObject:StyleValue = {
        fontSize: basic.event.config.overviewstyle?.fontsize || '32pt',
        color: basic.event.config.overviewstyle?.errorText || 'black',
        backgroundColor: basic.event.config.overviewstyle?.error || 'rgb(253, 108, 108)',
    };
    return baseObject;
});
const overviewObject:Ref<StyleValue> = computed(() => {
    let baseObject:StyleValue = {
        height: basic.event.config.overviewstyle?.height || '50px',
        fontSize: basic.event.config.overviewstyle?.titleSize || '20pt',
    };
    return baseObject;
});

</script>
<template>
    <div class="main-app overview-interface" v-if="showInterface()">
        <div class="overview-header" :style="overviewObject">
            <div class="logo" v-if="basic.event.config.overviewstyle?.logo">
                <img :src="basic.event.config.overviewstyle?.logo"/>
            </div>
            <div class="title">{{ basic.event.config.overviewstyle?.title }}</div>
        </div>
        <div class="table-wrapper">
            <table class="processed-list">
                <tbody>
                    <tr v-for="entity in processedDocumentList" :key="entity.id"  class="checkout-row" :style="entity.status == 'E' ? errorObject : finishedObject">
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="small">Finished</td>
                        <td class="name"><div class='subname'>{{ entity.name }}</div></td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                    </tr>
                    <tr v-for="entity in startedDocumentList" :key="entity.id" class="checkout-row" :style="startedObject">
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="small">Started</td>
                        <td class="name"><div class='subname'>{{ entity.name }}</div></td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                    </tr>
                    <tr v-for="entity in pendingDocumentList" :key="entity.id" class="checkout-row" :style="pendingObject">
                        <td v-if="basic.eventRequiresDocuments()" class="code">{{ entity.document }}</td>
                        <td class="small">Waiting</td>
                        <td class="name"><div class='subname'>{{ entity.name }}</div></td>
                        <td class="country">{{ getCountry(entity.countryId || 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>