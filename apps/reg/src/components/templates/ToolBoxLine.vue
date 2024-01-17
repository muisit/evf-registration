<script lang="ts" setup>
import type { PictureContent } from '../../../../common/api/schemas/template';
const props = defineProps<{
    type:string;
    picture?:PictureContent
}>();
const emits = defineEmits(['onSelect', 'onDelete']);

function selectThis(e:any)
{
    emits('onSelect', {event: e, type: props.type, picture: props.picture});
}

function deletePicture()
{
    emits('onDelete');
}

import { Avatar, CreditCard, Stamp, Location, Flag, Phone, Briefcase, Calendar, Document, Link, FullScreen, Picture, Delete } from '@element-plus/icons-vue';
import { ElIcon } from 'element-plus';
</script>
<template>
    <div class="toolboxline" @click="(e) => selectThis(e)">
        <ElIcon size="large">
            <Avatar v-if="props.type == 'photo'" />
            <CreditCard v-if="props.type == 'name'" />
            <Stamp v-if="props.type == 'category'" />
            <Location v-if="props.type == 'country'" />
            <Flag v-if="props.type == 'cntflag'" />
            <Phone v-if="props.type == 'org'" />
            <Briefcase v-if="props.type == 'roles'" />
            <Calendar v-if="props.type == 'dates'" />
            <Document v-if="props.type == 'text'" />
            <Link v-if="props.type == 'qr'" />
            <FullScreen v-if="props.type == 'box'" />
            <Picture v-if="props.type == 'image'" />
        </ElIcon>
        <span class="text" v-if="props.type == 'photo'" >Photo ID</span>
        <span class="text"  v-if="props.type == 'name'" >Name</span>
        <span class="text"  v-if="props.type == 'category'" >Category Designation</span>
        <span class="text"  v-if="props.type == 'country'" >Country Designation</span>
        <span class="text"  v-if="props.type == 'cntflag'" >Country Flag</span>
        <span class="text"  v-if="props.type == 'org'" >Organisation</span>
        <span class="text"  v-if="props.type == 'roles'" >Roles</span>
        <span class="text"  v-if="props.type == 'dates'" >Dates</span>
        <span class="text"  v-if="props.type == 'text'" >Text</span>
        <span class="text"  v-if="props.type == 'qr'" >QR Code</span>
        <span class="text"  v-if="props.type == 'box'" >Box</span>
        <span class="text"  v-if="props.type == 'img'" >
            {{ props.picture?.file_name }}
            <ElIcon class='trash-picture' size="small" @click="deletePicture">
                <Delete />
            </ElIcon>
        </span>
    </div>
</template>
