<script lang="ts" setup>
import { ref, watch, computed } from 'vue';
import type { Fencer } from '../../../../common/api/schemas/fencer';
import type { SideEvent } from '../../../../common/api/schemas/sideevent';
import type { Registration } from '../../../../common/api/schemas/registration';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { is_valid } from '../../../../common/functions';
import { selectEventsForFencer } from './lib/selectEventsForFencer';
import { determineUniqueTeamNames } from './lib/determineUniqueTeamNames';

const props = defineProps<{
    visible:boolean;
    fencer:Fencer;
    isadmin: boolean;
}>();
const emits = defineEmits(['onClose', 'onUnregister', 'onUpdate', 'onSave']);
const data = useDataStore();
const auth = useAuthStore();
const payments = ref('G');

watch(
    () => data.currentCountry,
    () => {
        payments.value = determineDefaultPayment(data.currentEvent.payments || 'group');
    },
    { immediate: true }
)

function determineDefaultPayment(eventPayment:string)
{
    if (!is_valid(data.currentCountry.id)) {
        return 'O';
    }
    else if (eventPayment == 'all' || eventPayment == 'group') {
        return 'G'; // by default, pay per group
    }
    // else eventPayment is not group and we are not on the Org page
    return 'I';
}

function closeForm()
{
    emits('onClose');
}

function unregister()
{
    emits('onUnregister');
    emits('onClose');
}

const availableEvents = computed(() => {
    return selectEventsForFencer(props.fencer).filter((event:SideEvent) => {
        if (!(event.isAthleteEvent || event.isNonCompetitionEvent || event.isRegistered)) {
            return false;
        }

        // if we are organisation, allow selecting the side-events, but not the competitions
        if(!is_valid(data.currentCountry.id) && !event.isNonCompetitionEvent) {
            return false;
        }
        return true;        
    });
});

function teamNames()
{
    return determineUniqueTeamNames(data.fencerData, availableEvents.value);
}

function updatePayment(e:string)
{
    console.log('updating payment', e);
    payments.value = e;
}

import PaymentSelection from './PaymentSelection.vue';
import EventSelection from './EventSelection.vue';
import RoleSelection from './RoleSelection.vue';
import AccreditationSelection from './AccreditationSelection.vue';
import { ElDialog, ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElButton, ElDatePicker } from 'element-plus';
</script>
<template>
    <ElDialog class='selection-dialog' :model-value="props.visible" title="Registration Selection" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <div class='selection-header'>
        <h3>{{ props.fencer.lastName }}, {{ props.fencer.firstName }}</h3>
        <h3 v-if="is_valid(data.currentCountry.id)">Year of birth: {{ props.fencer.birthYear }} Gender: {{ props.fencer.gender == 'F' ? 'Woman' : 'Man'}} Category: {{ props.fencer.category }}</h3>
      </div>
      <ElForm>
        <PaymentSelection :payments="payments" @on-update="updatePayment" :isadmin="props.isadmin"/>
        <EventSelection :fencer="props.fencer" :teams="teamNames()" :payments="payments" :available-events="availableEvents"/>
        <RoleSelection :fencer="props.fencer" :payments="payments"/>
        <AccreditationSelection v-if="auth.isOrganisation()" :fencer="props.fencer"/>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton v-if="props.isadmin" type="warning" @click="unregister">Unregister</ElButton>
          <ElButton type="primary" @click="closeForm">Close</ElButton>
        </span>
      </template>
    </ElDialog>
</template>