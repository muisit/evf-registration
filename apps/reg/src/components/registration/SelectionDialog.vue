<script lang="ts" setup>
import { ref, watch } from 'vue';
import { Fencer } from '../../../../common/api/schemas/fencer';
import { CountrySchema } from '../../../../common/api/schemas/country';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { is_valid } from '../../../../common/functions';

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
        <PaymentSelection :payments="payments" @on-update="(e) => payments = e" :isadmin="props.isadmin"/>
        <EventSelection :fencer="props.fencer" :payments="payments"/>
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