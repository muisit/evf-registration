<script lang="ts" setup>
import type { WeaponSchema } from '../../../../../common/api/schemas/weapon';
import { hasTeam } from '../../../../../common/lib/event';
import { useDataStore } from '../../../stores/data';
import { is_valid } from '../../../../../common/functions';
const emits = defineEmits(['onSort']);
const props = defineProps<{
    sorter:string[];
    sortable:boolean;
    weapon?:WeaponSchema;
}>();

const data = useDataStore();
function displayTeamColumn()
{
    return props.weapon && is_valid(props.weapon.id) && hasTeam(data.currentEvent);
}

import SortingIcon from './SortingIcon.vue';
</script>
<template>
    <thead>
        <tr v-if="props.weapon" class="preheader">
            <th colspan="10">
                {{ props.weapon.name || 'Support' }}
            </th>
        </tr>
        <tr>
            <th class="text-left">Surname <SortingIcon v-if="props.sortable" :sorter="props.sorter" name="n" @onSort="(e) => $emit('onSort', e)"/></th>
            <th class="text-left">First name <SortingIcon v-if="props.sortable" :sorter="props.sorter" name="f" @onSort="(e) => $emit('onSort', e)"/></th>
            <th class="text-center">Gender <SortingIcon v-if="props.sortable" :sorter="props.sorter" name="g" @onSort="(e) => $emit('onSort', e)"/></th>
            <th class="text-center">YOB <SortingIcon v-if="props.sortable" :sorter="props.sorter" name="y" @onSort="(e) => $emit('onSort', e)"/></th>
            <th class="text-center">Category <SortingIcon v-if="props.sortable" :sorter="props.sorter" name="c" @onSort="(e) => $emit('onSort', e)"/></th>
            <th class="text-left">
                <span v-if="!props.weapon">Role/Events</span>
                <span v-if="displayTeamColumn()">Team</span>
                <span v-if="props.weapon && !is_valid(props.weapon.id)">Role</span>
            </th>
            <th colspan="4"></th>
        </tr>
    </thead>
</template>