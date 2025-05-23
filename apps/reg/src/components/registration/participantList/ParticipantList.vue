<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { Fencer } from '../../../../../common/api/schemas/fencer';
import { hasTeam } from '../../../../../common/lib/event';
import { useDataStore } from '../../../stores/data';
import { sortAndFilterFencers } from '../lib/sortAndFilterFencers';
import { is_valid } from '../../../../../common/functions';
const emits = defineEmits(['onEdit', 'onSelect']);

const data = useDataStore();
const filter:Ref<Array<string>> = ref([]);
const sorter:Ref<Array<string>> = ref(['n', 'f']);
const dataList:Ref<Array<Fencer>> = ref([]);
const byweapon = ref(false);

function onFilter(filterState:any)
{
    // make the buttons exclusive by only allowing one of them to be checked
    var newFilter:Array<string> = []; // filter.value.filter((f) => f != filterState.name);
    if (filterState.state) {
        newFilter.push(filterState.name);
    }
    filter.value = newFilter;
}

watch(
    () => [data.currentEvent.id, data.currentCountry.id],
    () => {
        byweapon.value = hasTeam(data.currentEvent) && is_valid(data.currentCountry);
    },
    { immediate: true }
)

watch (
    () => [data.fencerData, filter.value, sorter.value],
    () => {
        dataList.value = sortAndFilterFencers(sorter.value, filter.value);
    },
    { immediate: true }
);

function onSort(newSorter:Array<string>)
{
    sorter.value = newSorter;
    dataList.value = sortAndFilterFencers(sorter.value, filter.value);
}

import FilterButton from '../FilterButton.vue';
import BasicParticipantList from './BasicParticipantList.vue';
import TeamParticipantList from './TeamParticipantList.vue';
import { ElSwitch } from 'element-plus';
</script>
<template>
    <div class="participant-list">
        <div class="participant-header">
            <div class="participant-filters" v-if="!hasTeam(data.currentEvent) && is_valid(data.currentCountry)">
                <FilterButton v-for="item in data.weapons" :key="item.id || 0" :name="item.abbr || ''" :label="item.name || ''" :filter="filter" @onFilter="onFilter"/>
                <FilterButton v-for="item in data.nonCompetitionEvents" :key="item.id || 0" :name="item.abbr || ''" :label="item.title || ''" :filter="filter" @onFilter="onFilter"/>
                <FilterButton name="Support" label="Support roles" :filter="filter" @onFilter="onFilter"/>
            </div>
            <div class="last" v-if="!hasTeam(data.currentEvent) && is_valid(data.currentCountry)">
                <ElSwitch v-model="byweapon" active-text="By Weapon" inactive-text="Individual" />
            </div>
        </div>
        <BasicParticipantList v-if="!byweapon" :dataList="dataList" :sorter="sorter" @on-edit="(e) => $emit('onEdit', e)" @on-select="(e) => $emit('onSelect', e)" @on-sort="onSort"/>
        <TeamParticipantList v-if="byweapon" :dataList="dataList" :filter="filter" @on-edit="(e) => $emit('onEdit', e)" @on-select="(e) => $emit('onSelect', e)"/>
    </div>
</template>