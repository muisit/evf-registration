<script lang="ts" setup>
import type { SideEvent } from "../../../../common/api/schemas/sideevent";
import type { Event } from "../../../../common/api/schemas/event";

const props = defineProps<{
    event: Event;
    side?:SideEvent;
    eventSymbol?:string;
}>();
const emits = defineEmits(['onUpdate']);

function update(field:string, value: string|number)
{
    emits('onUpdate', {field: field, value:value});
}

import { ElFormItem, ElInput, ElInputNumber, ElDatePicker, ElButton } from 'element-plus';
</script>
<template>
    <div class="sideevent-entry">
        <ElFormItem label="Title" v-if="props.side">
            <ElInput :model-value="props.side?.title || ''" @update:model-value="(e) => update('title', e)" />
        </ElFormItem>
        <ElFormItem label="Description" v-if="props.side">
            <ElInput :model-value="props.side?.description || ''" @update:model-value="(e) => update('description', e)" />
        </ElFormItem>
        <ElFormItem label="Starts" v-if="props.side">
            <ElDatePicker :model-value="props.side?.starts || '2020-01-01'" @update:model-value="(e) => update('starts', e)" value-format="YYYY-MM-DD"/>
        </ElFormItem>
        <ElFormItem label="Costs" v-if="props.side">
            <div><span class='currency'>{{ props.eventSymbol }}</span> <ElInputNumber :precision="2" :model-value="props.side?.costs || 0" @update:model-value="(e) => update('costs', e || 0)" /></div>            
        </ElFormItem>
        <ElFormItem v-if="!props.side" class="buttons">
            <ElButton @click="() => update('add', '')" type="primary">Add</ElButton>
        </ElFormItem>
    </div>
</template>