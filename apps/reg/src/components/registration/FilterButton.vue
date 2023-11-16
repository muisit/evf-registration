<script lang="ts" setup>
import { SideEvent } from '../../../../common/api/schemas/sideevent';
const props = defineProps<{
    filter:Array<string>;
    name: string;
    label: string;
}>();
const emits = defineEmits(['onFilter']);

function isSet()
{
    return props.filter.includes(props.name);
}

function onClick()
{
    emits('onFilter', {name: props.name, state: !isSet()});
}

import { ElCheckboxButton, ElTooltip } from 'element-plus';
</script>
<template>
    <ElTooltip :content="props.label" placement="bottom">
        <ElCheckboxButton @change="onClick" :model-value="isSet()">{{ props.name }}</ElCheckboxButton>
    </ElTooltip>
</template>