<script lang="ts" setup>
import { ref, computed } from 'vue';
import type { Ref } from 'vue';
import type { RoleSchema } from "../../../../common/api/schemas/role";
import type { Registration } from '../../../../common/api/schemas/registration';
import { defaultRegistration } from '../../../../common/api/schemas/registration';
import type { Fencer } from '../../../../common/api/schemas/fencer';
import { random_token, is_valid } from  '../../../../common/functions';
import { isOpenForRegistration } from '../../../../common/lib/event';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
const props = defineProps<{
    role:RoleSchema;
    fencer: Fencer;
}>();
const emits = defineEmits(['update']);
const data = useDataStore();
const auth = useAuthStore();

const registration:Ref<Registration> = computed(() => {
    if (props.fencer.registrations) {
        for(let i in props.fencer.registrations) {
            let reg = props.fencer.registrations[i];
            if (reg.roleId == props.role.id) {
                return reg;
            }
        }
    }
    return defaultRegistration();
});

function inputDisabled()
{
    if (auth.isHod() && !isOpenForRegistration(data.currentEvent)) return true;
    if (['error', 'saving', 'removing'].includes(registration.value.state || '')) return true;
    return false;
}

function checkboxValue()
{
    if (  (is_valid(registration.value.id) && ['', 'saved','saving'].includes(registration.value.state || ''))
       || (!is_valid(registration.value.id) && registration.value.state == 'saving')
    ) {
        return true;
    }
    return false;
}

function update(e:any)
{
    emits('update', e);
}

const checkbox:Ref<string> = ref(random_token(32));

const hasError = computed(() => registration.value.state == 'error');
const isSaving = computed(() => registration.value.state == 'saving' || registration.value.state == 'removing');
const isSaved = computed(() => registration.value.state == 'saved' || registration.value.state == 'removed');

import { Select, CloseBold, Upload } from '@element-plus/icons-vue';
import { ElCheckbox, ElSelect, ElOption, ElIcon } from 'element-plus';
</script>
<template>
    <tr>
        <td>
            <ElCheckbox :model-value="checkboxValue()" @update:model-value="update" :disabled="inputDisabled()" :id="checkbox"/>
        </td>
        <td>
            <label :for="checkbox">
                {{ props.role.name }}
            </label>
        </td>
        <td :class="{'state-icons':true, 'state-error': hasError, 'state-upload': isSaving, 'state-ok': isSaved}">
            <ElIcon>
                <CloseBold v-if="hasError" />
                <Select v-if="isSaved"/>
                <Upload v-if="isSaving"/>
            </ElIcon>
        </td>
        <td class="filler-cell"></td>
    </tr>
</template>