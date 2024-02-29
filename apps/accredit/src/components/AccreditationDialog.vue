<script lang="ts" setup>
import { ref } from 'vue';
import { is_valid, random_hash, valid_date } from '../../../common/functions';
import type { Registration } from '../../../common/api/schemas/registration';
import type { Fencer } from '../../../common/api/schemas/fencer';
import { useBasicStore } from '../../../common/stores/basic';
import { dayjs } from 'element-plus';

const props = defineProps<{
    visible:boolean;
    fencer?:Fencer;
}>();
const emits = defineEmits(['onClose', 'onUpdate', 'onCancel', 'onUnregister', 'onRegister']);
const basic = useBasicStore();
const reloadHash = ref(random_hash());

function cancelForm()
{
    emits('onCancel');
    emits('onClose');
}

function closeForm()
{
    emits('onClose');
}

function unregisterForm()
{
    emits('onUnregister');
}

function registerForm()
{
    emits('onRegister');
}


function update(registration:Registration, value: any)
{
    registration.state = value == 'P' ? 'P' : 'A';
    emits('onUpdate', {registration: registration});
}

function getRegistrationTitle(registration?:Registration)
{
    if (is_valid(registration?.sideEventId)) {
        let sideEvent = basic.sideEventsById['s' + registration?.sideEventId];
        if (sideEvent) {
            return sideEvent.title || '';
        }
    }
    if (is_valid(registration?.roleId)) {
        let role = basic.rolesById['r' + registration?.roleId];
        if (role) {
            return role.name || '';
        }
    }
    return 'Unknown';
}

function switchState(registration:Registration)
{
    return registration.state && registration.state == 'P' ? 'P' : 'A';
}

function switchStateLong(registration:Registration)
{
    return registration.state && registration.state == 'P' ? 'Present' : 'Absent';
}

function filteredRegistrations(switchable:boolean)
{
    if (switchable) {
        return props.fencer?.registrations?.filter((r:Registration) => is_valid(r.sideEventId)) || [];
    }
    else {
        return props.fencer?.registrations?.filter((r:Registration) => !is_valid(r.sideEventId)) || [];
    }
}

import { ElDialog, ElForm, ElFormItem, ElSwitch, ElButton } from 'element-plus';
import PhotoId from './special/PhotoId.vue';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Hand Out Accreditation" :close-on-click-modal="false"  :before-close="(done) => { cancelForm(); done(false); }">
      <PhotoId :fencer="props.fencer" :reloadHash="reloadHash"/>
      <div class="handout-dialog">
        <div class="field"><b>Name:</b> {{ props.fencer?.lastName }}, {{ props.fencer?.firstName }}</div>
        <div class="field"><b>Gender:</b> {{ props.fencer?.gender == 'F' ? 'Female' : 'Male' }}</div>
        <div class="field"><b>DOB:</b> {{ dayjs(props.fencer?.dateOfBirth).format('DD-MM-YYYY') }}</div>
        <div class="field">
            <b>Country:</b> {{ basic.countriesById['c' + props.fencer?.countryId]?.abbr }}: 
            {{ basic.countriesById['c' + props.fencer?.countryId]?.name }}
        </div>
        <ElForm>
            <ElFormItem v-for="reg in filteredRegistrations(true)" :key="reg.id || 0" :label="getRegistrationTitle(reg)">
                <ElSwitch :model-value="switchState(reg)"
                    active-value='P'
                    inactive-value='A'
                    @update:model-value="(e) => update(reg, e)" />
                    <span class='switch-state'>{{ switchStateLong(reg) }}</span>
            </ElFormItem>
        </ElForm>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="cancelForm">Cancel</ElButton>
          <ElButton type="info" @click="unregisterForm">Absent</ElButton>
          <ElButton type="info" @click="registerForm">Present</ElButton>
          <ElButton type="primary" @click="closeForm">Ok</ElButton>
        </span>
      </template>
    </ElDialog>
</template>