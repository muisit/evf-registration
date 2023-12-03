<script lang="ts" setup>
import { ref } from 'vue';
import type { Ref } from 'vue';
import type { RoleSchema } from "../../../../common/api/schemas/role";
import type { Registration } from '../../../../common/api/schemas/registration';
import { random_token, is_valid } from  '../../../../common/functions';

const props = defineProps<{
    role:RoleSchema;
    registration:Registration|null;
}>();
const emits = defineEmits(['onUpdate']);

function inputDisabled()
{
    if (props.registration?.state && props.registration?.state != 'saved') return true;
    return false;
}

function checkboxValue()
{
    return props.registration && is_valid(props.registration.id) ? true : false;
}

const checkbox:Ref<string> = ref(random_token(32));

import { Select, CloseBold, Upload } from '@element-plus/icons-vue';
import { ElCheckbox, ElSelect, ElOption, ElIcon } from 'element-plus';
</script>
<template>
    <tr>
        <td>
            <ElCheckbox :model-value="checkboxValue()" @update:model-value="(e) => $emit('onUpdate', e)" :disabled="inputDisabled()" :id="checkbox"/>
        </td>
        <td>
            <label :for="checkbox">
                {{ props.role.name }}
            </label>
        </td>
        <td :class="{'state-icons':true, 'state-error': props.registration?.state == 'error', 'state-upload': props.registration?.state == 'saving', 'state-ok': props.registration?.state == 'saved'}">
            <ElIcon>
                <CloseBold v-if="props.registration?.state == 'error'" />
                <Select v-if="props.registration?.state == 'saved'"/>
                <Upload v-if="props.registration?.state == 'saving'"/>
            </ElIcon>
        </td>
        <td class="filler-cell"></td>
    </tr>
</template>