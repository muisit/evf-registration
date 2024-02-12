<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from 'vue';
import type { Ref } from 'vue';
import type { Code } from '../../../common/api/schemas/codes';
import type { Fencer, FencerById } from '../../../common/api/schemas/fencer';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();

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
    currentEntity.value.card = codeObject;
}

function documentDispatcher(code:string, codeObject:Code)
{
    currentEntity.value.document = codeObject;
}

function failDispatcher(code:string, codeObject:Code)
{
    processedList.value.unshift(currentEntity.value);
    currentEntity.value = {};
}

onMounted(() => {
    data.subtitle = "Accreditation Handout";
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

</script>
<template>
    <div class="checkin-interface" v-if="auth.isCheckin(auth.eventId, 'code')">
        <div class="table-wrapper">
            <table class="processed-list style-stripes">
                <tbody>
                    <tr v-for="(entity, i) in processedList" :key="i" class="checkin-row">
                        <td class="code">{{ entity.badge?.original }}</td>
                        <td class="lastname">{{ fencers[entity.badge?.original || '']?.lastName || '' }}</td>
                        <td class="firstname">{{ fencers[entity.badge?.original || '']?.firstName || '' }}</td>
                        <td class="country">{{ getCountry(fencers[entity.badge?.original || '']) }}</td>
                        <td class="code">{{ entity.card?.original }}</td>
                        <td class="code">{{ entity.document?.original }}</td>
                        <td class="date">{{ entity.entered }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>