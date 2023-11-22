<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { is_valid } from '../../../../common/functions';
import type { Fencer } from '../../../../common/api/schemas/fencer';
import type { Registration } from '../../../../common/api/schemas/registration';
import { sortAndFilterFencers } from './lib/sortAndFilterFencers';
import { useDataStore } from '../../stores/data';
const emits = defineEmits(['onEdit', 'onSelect']);

const data = useDataStore();
const sorter:Ref<Array<string>> = ref(['n', 'f']);
const filter:Ref<Array<string>> = ref([]);
const dataList:Ref<Array<Fencer>> = ref([]);

function getRolesAndEvents(fencer:Fencer)
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
                        athleteRoles.push(se.abbr);
                    }
                    else {
                        nonAthleteRoles.push(se.title);
                    }
                }
            }
            else if(is_valid(reg.roleId || '')) {
                var role = data.rolesById['r' + reg.roleId];
                if (role) {
                    otherRoles.push(role.name);
                }
            }
        });
    }
    athleteRoles = athleteRoles.concat(nonAthleteRoles).concat(otherRoles);
    return athleteRoles.join(', ');
}

watch (
    () => data.fencerData,
    () => {
        console.log('fencer data has changed, creating new participant list');
        dataList.value = sortAndFilterFencers(sorter.value, filter.value);
    },
    { immediate: true }
)


function fencerSelect(item:Fencer)
{
    emits('onEdit', item);
}

function eventSelect(item:Fencer)
{
    emits('onSelect', item);
}

function onSort(newSorter:Array<string>)
{
    sorter.value = newSorter;
    console.log('sorting has changed, creating new participant list');
    dataList.value = sortAndFilterFencers(sorter.value, filter.value);
}

function onFilter(filterState)
{
    var newFilter:Array<string> = filter.value.filter((f) => f != filterState.name);
    if (filterState.state) {
        newFilter.push(filterState.name);
    }
    filter.value = newFilter;
    dataList.value = sortAndFilterFencers(sorter.value, filter.value);
}

import PhotoIcon from './PhotoIcon.vue';
import SortingIcon from './SortingIcon.vue';
import FilterButton from './FilterButton.vue';
import { ElIcon } from 'element-plus';
import { Edit, Trophy } from '@element-plus/icons-vue';
</script>
<template>
    <div class="participant-list">
        <div class="participant-filters">
            <FilterButton v-for="item in data.weapons" :key="item.id" :name="item.abbr" :label="item.name" :filter="filter" @onFilter="onFilter"/>
            <FilterButton v-for="item in data.nonCompetitionEvents" :key="item.id" :name="item.abbr" :label="item.title" :filter="filter" @onFilter="onFilter"/>
            <FilterButton name="Support" label="Support roles" :filter="filter" @onFilter="onFilter"/>
        </div>
        <table class="style-stripes">
            <thead>
                <tr>
                    <th class="text-left">Name <SortingIcon :sorter="sorter" name="n" @onSort="onSort"/></th>
                    <th class="text-left">First name <SortingIcon :sorter="sorter" name="f" @onSort="onSort"/></th>
                    <th class="text-center">Gender <SortingIcon :sorter="sorter" name="g" @onSort="onSort"/></th>
                    <th class="text-center">YOB <SortingIcon :sorter="sorter" name="y" @onSort="onSort"/></th>
                    <th class="text-center">Category <SortingIcon :sorter="sorter" name="c" @onSort="onSort"/></th>
                    <th class="text-left">Role/Events</th>
                    <th colspan="3"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in dataList" :key="item.id">
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
                </tr>
            </tbody>
        </table>
    </div>
</template>