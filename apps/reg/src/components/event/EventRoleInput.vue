<script lang="ts" setup>
import { computed } from 'vue';
import type { Event } from "../../../../common/api/schemas/event";
import type { EventRole, EventUser } from "../../../../common/api/schemas/eventroles";

const props = defineProps<{
    event: Event;
    role?:EventRole;
    users:EventUser[];
}>();
const emits = defineEmits(['onUpdate']);

function update(field:string, value: string|number)
{
    emits('onUpdate', {field: field, value:value});
}

const userValues = computed(() => {
    let retval:any = [];
    retval.push({text:'Select a user', value: 0});
    props.users.map((u:EventUser) => {
        retval.push({text: u.name, value: u.id});
    });
    return retval;
});

const roleValues = [
    { text: 'Select a role', value: ''},
    { text: 'Organiser', value: 'organiser'},
    { text: 'Registrar', value: 'registrar'},
    { text: 'Cashier', value: 'cashier'},
    { text: 'Accreditator', value: 'accreditation'},
]

import { ElFormItem, ElSelect, ElOption, ElButton } from 'element-plus';
</script>
<template>
    <div class="sideevent-entry">
        <ElFormItem label="Role" v-if="props.role" class="roles">
            <ElSelect :model-value="props.role.userId || 0" @update:model-value="(e) => update('user', e)">
                <ElOption v-for="(opt, i) in userValues" :key="i" :label="opt.text" :value="opt.value" />
            </ElSelect>
            <ElSelect :model-value="props.role.role || ''" @update:model-value="(e) => update('role', e)">
                <ElOption v-for="(opt, i) in roleValues" :key="i" :label="opt.text" :value="opt.value" />
            </ElSelect>
        </ElFormItem>
        <ElFormItem v-if="!props.role" class="buttons">
            <ElButton @click="() => update('add', '')" type="primary">Add</ElButton>
        </ElFormItem>
    </div>
</template>