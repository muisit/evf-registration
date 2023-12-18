<script lang="ts" setup>
import { is_valid } from '../../../../../common/functions';
import type { Fencer } from '../../../../../common/api/schemas/fencer';
import type { Registration } from '../../../../../common/api/schemas/registration';
import { allowMoreTeams } from '../../../../../common/lib/event';
import type { WeaponSchema } from '../../../../../common/api/schemas/weapon';
import { useDataStore } from '../../../stores/data';
const emits = defineEmits(['onEdit', 'onSelect']);
const props = defineProps<{
    dataList: Fencer[];
    weapon?: WeaponSchema;
}>();

const data = useDataStore();

function getRolesAndEvents(fencer:Fencer)
{
    if (!props.weapon) {
        return parseAllRolesAndEvents(fencer);
    }
    else {
        return parseRolesForWeaponOnly(fencer);
    }
}

function parseRolesForWeaponOnly(fencer:Fencer)
{
    var athleteRoles:Array<string> = [];
    var nonAthleteRoles:Array<string> = [];
    var otherRoles:Array<string> = [];
    if (fencer.registrations) {
        fencer.registrations.forEach((reg: Registration) => {
            if (is_valid(reg.sideEventId || '')) {
                var se = data.sideEventsById['s' + reg.sideEventId];
                if (se && se.competition && se.competition.weapon && props.weapon && props.weapon.id == se.competition.weapon.id) {
                    // only display the team if we allow more teams
                    if (se.competition.category?.type == 'T') {
                        if(allowMoreTeams(data.currentEvent)) {
                            athleteRoles.push('' + se.competition.category?.name + ' ' + (reg.team || ''));
                        }
                        else {
                            // indicate team grand-veterans or veterans
                            athleteRoles.push(se.competition.category?.name || '');
                        }
                    }
                }
                else if(se && !se.competition && !is_valid(props.weapon?.id)) {
                    nonAthleteRoles.push(se.title);
                }
            }
            else if(is_valid(reg.roleId || '') && !is_valid(props.weapon?.id)) {
                var role = data.rolesById['r' + reg.roleId];
                if (role) {
                    otherRoles.push(role.name || '');
                }
            }
        });
    }
    athleteRoles = athleteRoles.concat(nonAthleteRoles).concat(otherRoles);
    return athleteRoles.join(', ');
}

function parseAllRolesAndEvents(fencer:Fencer)
{
    var athleteRoles:Array<string> = [];
    var nonAthleteRoles:Array<string> = [];
    var otherRoles:Array<string> = [];
    if (fencer.registrations) {
        fencer.registrations.forEach((reg: Registration) => {
            if (is_valid(reg.sideEventId || '')) {
                var se = data.sideEventsById['s' + reg.sideEventId];
                if (se) {
                    if (se.competition) {
                        if (se.competition.category?.type == 'T' && allowMoreTeams(data.currentEvent)) {
                            athleteRoles.push(se.abbr + ' (' + reg.team + ')');
                        }
                        else {
                            athleteRoles.push(se.abbr || '');
                        }
                    }
                    else {
                        nonAthleteRoles.push(se.title);
                    }
                }
            }
            else if(is_valid(reg.roleId || '')) {
                var role = data.rolesById['r' + reg.roleId];
                if (role) {
                    otherRoles.push(role.name || '');
                }
            }
        });
    }
    athleteRoles = athleteRoles.concat(nonAthleteRoles).concat(otherRoles);
    return athleteRoles.join(', ');
}

function fencerSelect(item:Fencer)
{
    emits('onEdit', item);
}

function eventSelect(item:Fencer)
{
    emits('onSelect', item);
}

function filterErrors(fencer:Fencer)
{
    let errors:string[] = [];
    fencer.registrations?.map((reg:Registration) => {
        if (!props.weapon) {
            if (reg.errors && reg.errors.length) {
                errors = errors.concat(reg.errors);
            }
        }
        else if (is_valid(props.weapon.id)) { // support registrations have no rules
            let se = data.sideEventsById['s' + reg.sideEventId];
            if (se && se.competition && se.competition.weapon && se.competition.weapon.id == props.weapon.id) {
                if (reg.errors && reg.errors.length) {
                   errors = errors.concat(reg.errors);
                }
            }
        }
    });
    errors = errors.sort().filter((e,i,a) => i === a.indexOf(e));
    return errors;
}

import PhotoIcon from './PhotoIcon.vue';
import { ElIcon, ElTooltip } from 'element-plus';
import { Edit, Trophy, Bell, Camera, Close } from '@element-plus/icons-vue';
</script>
<template>
    <tbody>
        <tr v-for="item in props.dataList" :key="item.id" :class="{hasErrors: filterErrors(item).length > 0}" :data-errors="JSON.stringify(filterErrors(item))">
            <td class='text-left'>{{ item.lastName }}</td>
            <td class='text-left'>{{ item.firstName }}</td>
            <td class='text-center'>{{ item.fullGender }}</td>
            <td class='text-center'>{{ item.birthYear }}</td>
            <td class='text-center'>{{ item.category }}</td>
            <td class='text-left'>{{ getRolesAndEvents(item) }}</td>
            <td class='text-center'>
                <PhotoIcon :fencer="item" />
            </td>
            <td class='text-center'>
                <ElIcon>
                    <Edit @click="() => fencerSelect(item)"/>
                </ElIcon>
            </td>
            <td class='text-center'>
                <ElIcon>
                    <Trophy @click="() => eventSelect(item)"/>
                </ElIcon>
            </td>
            <td class="text-center">
                <ElIcon v-if="filterErrors(item).length > 0">
                    <ElTooltip :content="filterErrors(item).join(', ')">
                        <Bell />
                    </ElTooltip>
                </ElIcon>
            </td>
        </tr>
    </tbody>
</template>