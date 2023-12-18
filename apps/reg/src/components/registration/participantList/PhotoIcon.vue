<script lang="ts" setup>
const props = defineProps<{
    fencer:any;
}>();

function isAbsent()
{
    return !isPending() && !isAccepted() && !doReplace();
}

function isPending()
{
    return props.fencer.photoStatus == 'Y';
}

function isAccepted()
{
    return props.fencer.photoStatus == 'A';
}

function doReplace()
{
    return props.fencer.photoStatus == 'R';
}

function displayMessage()
{
    if (isAbsent()) {
        return "No photo found, please upload a photo";
    }
    else if(isPending()) {
        return "Photo uploaded, waiting for approval";
    }
    else if(doReplace()) {
        return "Photo was not approved. Please upload a new photo";
    }
    else if(isAccepted()) {
        return "Photo uploaded and approved";
    }
}

import { ElIcon, ElTooltip } from 'element-plus';
import { Camera, CloseBold } from '@element-plus/icons-vue';
</script>
<template>
    <ElIcon :class="{photoAbsent: isAbsent(), photoPending: isPending(), photoAccepted: isAccepted(), photoReplace: doReplace()}">
        <ElTooltip :content="displayMessage()">
            <Camera v-if="!isAbsent()"/>
            <CloseBold v-if="isAbsent()"/>
        </ElTooltip>
    </ElIcon>
</template>