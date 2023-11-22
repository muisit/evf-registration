<script lang="ts" setup>
import { ref } from 'vue';
import { Fencer } from '../../../../common/api/schemas/fencer';
import { RoleSchema } from '../../../../common/api/schemas/role';
import { useDataStore } from '../../stores/data';
import { selectRolesForFencer } from './lib/selectRolesForFencer';
import { is_valid } from '../../../../common/functions';
const props = defineProps<{
    fencer: Fencer;
    teams: string[];
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
        data.saveRegistration(props.fencer, null, '' + role.id, null, props.payments);
    }
    else {
        data.deleteRegistration(props.fencer, null, '' + role.id);
    }
}

function isRegistered(role:RoleSchema)
{
    if (props.fencer.registrations) {
        for(let i = 0;i < props.fencer.registrations.length; i++) {
            if (props.fencer.registrations[i].roleId == role.id) {
                return props.fencer.registrations[i];
            }
        }
    }
    return null;
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
                    :key="role.id"
                    :role="role"
                    :registration="isRegistered(role)"
                    @on-update="(e) => saveRegistration(role, e)"
                />
            </tbody>
        </table>
    </div>
</template>
