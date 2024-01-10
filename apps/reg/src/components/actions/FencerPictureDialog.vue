<script lang="ts" setup>
import { ref } from 'vue';
import { random_hash } from '../../../../common/functions';
import type { Fencer } from '../../../../common/api/schemas/fencer';
import { useDataStore } from '../../stores/data';
import { savephotostate } from '../../../../common/api/fencers/savephotostate';
import { uploadphoto } from '../../../../common/api/fencers/uploadphoto';

const props = defineProps<{
    visible:boolean;
    fencer:Fencer;
    hasNext: boolean;
    hasPrevious: boolean;
}>();
const emits = defineEmits(['onClose', 'onUpdate', 'onSave', 'goto']);
const reloadHash = ref(random_hash());

function closeForm()
{
    emits('onClose');
}

function saveFencerPhoto(fileObject:any)
{
    return uploadphoto(props.fencer, fileObject)
        .then((data) => {
            if (data && data.status == "ok") {
                update('photoStatus', 'Y');
                emits('onSave');
                reloadHash.value = random_hash();
            }
    });
}

function update(fieldName:string, value: any)
{
    // immediately save the new photoStatus
    let fencer = Object.assign({}, props.fencer);
    fencer.photoStatus = value;
    emits('onUpdate', {field: fieldName, value: value});
    savephotostate(fencer).then(() => {
        emits('onSave');
    });
}

function goToPrevious()
{
    emits('goto', 'previous');
}

function goToNext()
{
    emits('goto', 'next');
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElButton, ElDatePicker } from 'element-plus';
import PhotoId from '../registration/PhotoId.vue';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Edit Fencer Picture" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <h3>{{ props.fencer.fullName }}, {{ props.fencer.country?.name }}</h3>
      <h3>Birthyear: {{ props.fencer.birthYear }} Gender: {{ props.fencer.gender == 'M' ? 'Male': 'Female' }}</h3>
      <ElForm>
        <ElFormItem>
          <PhotoId @onSave="saveFencerPhoto" :fencer="props.fencer" :dataComplete="true" @onStateChange="(e) => update('photoStatus', e)" :reloadHash="reloadHash"/>
        </ElFormItem>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="primary" @click="goToPrevious" :disabled="!props.hasPrevious">Previous</ElButton>
          <ElButton type="primary" @click="goToNext" :disabled="!props.hasNext">Next</ElButton>
          <ElButton type="warning" @click="closeForm">Close</ElButton>
        </span>
      </template>
    </ElDialog>
</template>