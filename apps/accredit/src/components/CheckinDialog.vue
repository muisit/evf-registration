<script lang="ts" setup>
import { ref } from 'vue';
import { is_valid, parse_date, random_hash, valid_date } from '../../../common/functions';
import type { Registration } from '../../../common/api/schemas/registration';
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { Code } from '../../../common/api/schemas/codes';
import { useBasicStore } from '../../../common/stores/basic';
import { dayjs } from 'element-plus';

const props = defineProps<{
    visible:boolean;
    fencer?:Fencer|null;
    card?:Code|null;
    document?:Code|null;
}>();
const emits = defineEmits(['onClose', 'onCancel', 'onSubmit']);
const basic = useBasicStore();
const reloadHash = ref(random_hash());

function cancelForm()
{
    emits('onCancel');
}

function closeForm()
{
    emits('onSubmit');
}

function getRegistrationDate(registration?:Registration)
{
    if (is_valid(registration?.sideEventId)) {
        let sideEvent = basic.sideEventsById['s' + registration?.sideEventId];
        if (sideEvent) {
            return parse_date(sideEvent.starts).format('ddd D');
        }
    }
    return '';
}

function getRegistrationTitle(registration?:Registration)
{
    if (is_valid(registration?.sideEventId)) {
        let sideEvent = basic.sideEventsById['s' + registration?.sideEventId];
        if (sideEvent) {
            return sideEvent.title || '';
        }
    }
    return '';
}

function sortedRegistrations()
{
    return (props.fencer?.registrations || []).filter((r:Registration) => {
        let se = basic.sideEventsById['s' + r.sideEventId];
        return (se && is_valid(se.competitionId));
    }).sort((a, b) => {
        let se1 = basic.sideEventsById['s' + a.sideEventId];
        let se2 = basic.sideEventsById['s' + b.sideEventId];
        let dt1 = parse_date(se1.starts).format('YYYYMMDD');
        let dt2 = parse_date(se2.starts).format('YYYYMMDD');
        if (dt1 == dt2) return 0;
        return dt1 > dt2 ? -1 : 1;
    })
}

import { ElDialog, ElForm, ElFormItem, ElSwitch, ElButton } from 'element-plus';
import PhotoId from './special/PhotoId.vue';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Weapon Control Check-in" :close-on-click-modal="false"  :before-close="(done) => { cancelForm(); done(false); }">
      <PhotoId v-if="props.fencer" :fencer="props.fencer" :reloadHash="reloadHash"/>
      <div class="checkin-dialog">
        <div class="field"><b>Name:</b> {{ props.fencer?.lastName }}, {{ props.fencer?.firstName }}</div>
        <div class="field"><b>Gender:</b> {{ props.fencer?.gender == 'F' ? 'Female' : 'Male' }}</div>
        <div class="field">
            <b>Country:</b> {{ basic.countriesById['c' + props.fencer?.countryId]?.abbr }}: 
            {{ basic.countriesById['c' + props.fencer?.countryId]?.name }}
        </div>
        <div class="field">
            <div v-for="reg in sortedRegistrations()" :key="reg.id || 0" class="sideevent">
                <div class='date'>{{ getRegistrationDate(reg) }}</div><div class="title">{{ getRegistrationTitle(reg) }}</div>
            </div>
        </div>
        <div class="card" v-if="props.card">
            Card: {{ props.card.data }}
        </div>
        <div class="card" v-if="props.document">
            Document: {{ props.document.data }}
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="cancelForm">Cancel</ElButton>
          <ElButton type="primary" @click="closeForm">Ok</ElButton>
        </span>
      </template>
    </ElDialog>
</template>