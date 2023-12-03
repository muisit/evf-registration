<script lang="ts" setup>
import { useAuthStore } from '../../../../common/stores/auth';
import { useDataStore } from '../../stores/data';
const props = defineProps<{
    fencer:any;
    dataComplete:boolean;
    reloadHash: string;
}>();
const emits = defineEmits(['onSave', 'onStateChange']);

const auth = useAuthStore();
const data = useDataStore();

function hasPicture()
{
    return ['Y','A','R'].includes(props.fencer.photoStatus);
}

function canApprove()
{
    // system administrators, the event organiser and the event registrar can change photo's. 
    return auth.isSysop() || auth.isOrganiser(data.currentEvent.id)  || auth.isRegistrar(data.currentEvent.id);
}

function getPhotoUrl()
{
    return import.meta.env.VITE_API_URL + '/fencers/' + props.fencer.id + '/photo?hash=' + props.reloadHash;
}

function approveStates()
{
    var approvestates=[{
        name: "Newly uploaded",
        id: "Y"
    },{
        name: "Approved",
        id: "A"
    },{
        name: "Request replacement",
        id: "R"
    }];

    if (!hasPicture()) {
        approvestates=[{
            name: 'None available',
            id: 'N'    
        }];
    }
    return approvestates;
}

function onFileChange(event:any)
{
    if (event.target.files && event.target.files.length) {
        emits('onSave', event.target.files[0]);
    }
}

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="photoid-element">
        <div class='photo-id' v-if="hasPicture()">
            <img class='photo-id-image' :src="getPhotoUrl()"/>
        </div>
        <div class='text-center'>
            <input type="file" @change="onFileChange" v-if="props.dataComplete"/>
        </div>
        <div v-if="canApprove()" class="approval-dropdown">
            <ElSelect :model-value="props.fencer.photoStatus" @update:model-value="(e) => $emit('onStateChange', e)">
                <ElOption v-for="state in approveStates()" :key="state.id" :value="state.id" :label="state.name" />
            </ElSelect>
        </div>
    </div>
</template>