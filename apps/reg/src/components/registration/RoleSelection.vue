<script lang="ts" setup>
import type { Fencer } from '../../../../common/api/schemas/fencer';
import type { RoleSchema } from '../../../../common/api/schemas/role';
import { useDataStore } from '../../stores/data';
import { selectRolesForFencer } from './lib/selectRolesForFencer';
import { is_valid } from '../../../../common/functions';
const props = defineProps<{
    fencer: Fencer;
    payments: string;
}>();

const data = useDataStore();

function availableRoles():RoleSchema[]
{
    return selectRolesForFencer(props.fencer).filter((role:RoleSchema) => {
        if(!is_valid(data.currentCountry.id) && role.type == 'Country') return false;
        else if(is_valid(data.currentCountry.id) && role.type != 'Country') return false;
        return true;
    });
}

function saveRegistration(role:RoleSchema, state:any)
{
    if (state) {
        data.saveRegistration(props.fencer, null, role.id, null, props.payments);
    }
    else {
        data.removeRegistration(props.fencer, null, role.id);
    }
}

import SelectableRole from './SelectableRole.vue';
</script>
<template>
    <div class="role-selection">
        <h3>Support Roles</h3>
        <table class='fencer-select-roles'>
            <tbody>
                <SelectableRole
                    v-for="role in availableRoles()"
                    :key="role.id || 0"
                    :role="role"
                    :fencer="props.fencer"
                    @update="(e) => saveRegistration(role, e)"
                />
            </tbody>
        </table>
    </div>
</template>
