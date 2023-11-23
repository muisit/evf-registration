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

import { ElIcon } from 'element-plus';
import { Camera, CloseBold } from '@element-plus/icons-vue';
</script>
<template>
    <ElIcon :class="{photoAbsent: isAbsent(), photoPending: isPending(), photoAccepted: isAccepted(), photoReplace: doReplace()}">
        <Camera v-if="!isAbsent()"/>
        <CloseBold v-if="isAbsent()"/>
    </ElIcon>
</template>