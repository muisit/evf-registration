<script lang="ts" setup>
const props = defineProps<{
    sorter:Array<string>;
    name:string;
}>();
const emits = defineEmits(['onSort']);

function findState() {
    if (props.sorter.includes(props.name)) {
        return 1;
    }
    if (props.sorter.includes(props.name.toUpperCase())) {
        return -1;
    }
    return 0;
}

function isUp() {
    return findState() == 1;
}

function isDown() {
    return findState() == -1;
}

function isUnset() {
    return findState() == 0;
}

function createSorter()
{
    var newSorter = props.sorter.filter((el:string) => el.toLowerCase() !== props.name);
    var state = findState();
    if (state == 0 || state == -1) {
        newSorter.unshift(props.name);
    }
    else if(state == 1) {
        newSorter.unshift(props.name.toUpperCase());
    }
    emits('onSort', newSorter);
}

import { ElIcon } from 'element-plus';
import { CaretTop, CaretBottom, DCaret } from '@element-plus/icons-vue';
</script>
<template>
     <ElIcon class="sorting-icon" @click="createSorter()">
        <CaretTop v-if="isUp()"/>
        <CaretBottom v-if="isDown()"/>
        <DCaret v-if="isUnset()"/>
    </ElIcon>
</template>