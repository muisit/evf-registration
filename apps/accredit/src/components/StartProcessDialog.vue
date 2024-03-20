<script lang="ts" setup>
import { ref } from 'vue';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import { savedocument } from '../../../common/api/accreditations/savedocument';
import { useBasicStore } from '../../../common/stores/basic';
import { useAuthStore } from '../../../common/stores/auth';

const props = defineProps<{
    visible:boolean;
    document?:AccreditationDocument|null;
}>();
const emits = defineEmits(['onCancel', 'onSubmit']);
const basic = useBasicStore();
const auth = useAuthStore();

function cancelForm()
{
    emits('onCancel');
}

function startProcess()
{
    saveStatus('P');
}

function returnProcess()
{
    saveStatus('C');
}

function endProcess()
{
    saveStatus('G');
}

function errorProcess()
{
    saveStatus('E');
}

function saveStatus(status:string)
{
    auth.isLoading('savedocument');
    savedocument({id: props.document?.id || 0, status: status}).then((dt) => {
        auth.hasLoaded('savedocument');
        if (dt) {
            emits('onSubmit', dt);
        }
        else {
            throw new Error("invalid response");
        }
    })
    .catch((e) => {
        console.log(e);
        auth.hasLoaded('savedocument');
        alert("There was an error saving the state of this bag. Please reload the page and try again");
    });
}

function startEnabled()
{
    return basic.eventMarksStartOfProcessing() && !props.document?.processStart;
}

function returnEnabled()
{
    return basic.eventMarksStartOfProcessing() && props.document?.processStart && !props.document?.processEnd;
}

function endEnabled()
{
    return !basic.eventMarksStartOfProcessing() || (props.document?.processStart && !props.document?.processEnd);
}

import { ElDialog, ElForm, ElFormItem, ElSwitch, ElButton } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Weapon Control Process" :close-on-click-modal="false"  :before-close="(done) => { cancelForm(); done(false); }">
      <div class="startprocess-dialog">
        <div class="field"><b>Name:</b> {{ props.document?.name }}</div>
        <div class="field"><b>Country:</b> {{ basic.countriesById['c' + props.document?.countryId]?.name }}</div>
        <div class="field"><b>Dates:</b> {{ props.document?.dates?.join(', ') }}</div>
        <div class="field" v-if="props.document?.card"><b>Card:</b> {{ props.document?.card }}</div>
        <div class="field" v-if="props.document?.document"><b>Document:</b> {{ props.document?.document }}</div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="cancelForm">Cancel</ElButton>
          <ElButton type="primary" @click="startProcess" v-if="startEnabled()">Start</ElButton>
          <ElButton type="info" @click="returnProcess" v-if="returnEnabled()">Return</ElButton>
          <ElButton type="warning" @click="errorProcess" v-if="endEnabled()">Mark with Faults</ElButton>
          <ElButton type="primary" @click="endProcess" v-if="endEnabled()">End</ElButton>
        </span>
      </template>
    </ElDialog>
</template>