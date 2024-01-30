<script lang="ts" setup>
import { is_valid } from '../../../../../common/functions';
import type { Fencer } from '../../../../../common/api/schemas/fencer';
import type { Registration } from '../../../../../common/api/schemas/registration';
import type { SideEvent } from '../../../../../common/api/schemas/sideevent';
import { allowMoreTeams } from '../../../../../common/lib/event';
import { useDataStore } from '../../../stores/data';
const emits = defineEmits(['onEdit', 'onSelect']);
const props = defineProps<{
    dataList: Fencer[];
    event?: SideEvent;
}>();

const data = useDataStore();

function getRolesAndEvents(fencer:Fencer)
{
    if (!props.event) {
        return parseAllRolesAndEvents(fencer);
    }
    else {
        return parseRolesForCompetitionOnly(fencer);
    }
}

function parseRolesForCompetitionOnly(fencer:Fencer)
{
    var athleteRoles:Array<string> = [];
    var nonAthleteRoles:Array<string> = [];
    var otherRoles:Array<string> = [];
    if (fencer.registrations) {
        fencer.registrations.forEach((reg: Registration) => {
            if (is_valid(reg.sideEventId || '')) {
                var se = data.sideEventsById['s' + reg.sideEventId];
                if (se && props.event && se.id == props.event.id) {
                    // only display the team if we allow more teams
                    if (se.competition?.category?.type == 'T') {
                        if(allowMoreTeams(data.currentEvent)) {
                            athleteRoles.push('' + se.competition.category?.name + ' ' + (reg.team || ''));
                        }
                    }
                }
                else if(se && !se.competition && !is_valid(props.event?.id)) {
                    nonAthleteRoles.push(se.title);
                }
            }
            else if(is_valid(reg.roleId || '') && !is_valid(props.event?.id)) {
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
        if (!props.event) {
            if (reg.errors && reg.errors.length) {
                errors = errors.concat(reg.errors);
            }
        }
        else if (is_valid(props.event.id)) { // support registrations have no rules
            let se = data.sideEventsById['s' + reg.sideEventId];
            if (se && props.event.id == se.id) {
                if (reg.errors && reg.errors.length) {
                   errors = errors.concat(reg.errors);
                }
            }
        }
    });
    errors = errors.sort().filter((e,i,a) => i === a.indexOf(e));

    // temporary error to mark people that were just unregistered completely
    // This only shows in the individual list, because they are no longer part
    // of a team or the support group
    if (!fencer.registrations || fencer.registrations.length == 0) {
        errors = ["This person is not registered for anything yet"];
    }

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
                    <ElTooltip content="Adjust fencer information and photo">
                        <Edit @click="() => fencerSelect(item)"/>
                    </ElTooltip>
                </ElIcon>
            </td>
            <td class='text-center'>
                <ElIcon>
                    <ElTooltip content="Select competitions and roles">
                        <Trophy @click="() => eventSelect(item)"/>
                    </ElTooltip>
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