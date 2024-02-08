<script lang="ts" setup>
import { useBasicStore } from '../../../../common/stores/basic';
import { useDataStore } from '../../stores/data';
const props = defineProps<{
    fencer:any;
    reloadHash: string;
}>();
const emits = defineEmits(['onSave', 'onStateChange']);

const basic = useBasicStore();
const data = useDataStore();

function hasPicture()
{
    return ['Y','A','R'].includes(props.fencer.photoStatus);
}

function getPhotoUrl()
{
    return import.meta.env.VITE_API_URL + '/fencers/' + props.fencer.id + '/photo?event=' + basic.event.id + '&hash=' + props.reloadHash;
}

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="photoid-element">
        <div class='photo-id' v-if="hasPicture()">
            <img class='photo-id-image' :src="getPhotoUrl()"/>
        </div>
    </div>
</template>