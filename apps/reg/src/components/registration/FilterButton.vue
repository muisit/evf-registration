<script lang="ts" setup>
import { SideEvent } from '../../../../common/api/schemas/sideevent';
const props = defineProps<{
    filter:Array<string>;
    name: string;
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

import { ElCheckboxButton } from 'element-plus';
</script>
<template>
  <ElCheckboxButton @change="onClick" :model-value="isSet()">{{ props.name }}</ElCheckboxButton>
</template>