<script lang="ts" setup>
import { ref, Ref, watch } from 'vue';
import { Fencer } from '../../../../../common/api/schemas/fencer';
import { useDataStore } from '../../../stores/data';
import { sortAndFilterFencers } from '../lib/sortAndFilterFencers';
const emits = defineEmits(['onEdit', 'onSelect']);

const data = useDataStore();
const filter:Ref<Array<string>> = ref([]);
const sorter:Ref<Array<string>> = ref(['n', 'f']);
const dataList:Ref<Array<Fencer>> = ref([]);
const byweapon = ref(false);

function onFilter(filterState)
{
    var newFilter:Array<string> = filter.value.filter((f) => f != filterState.name);
    if (filterState.state) {
        newFilter.push(filterState.name);
    }
    filter.value = newFilter;
}

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
            <div class="participant-filters">
                <FilterButton v-for="item in data.weapons" :key="item.id" :name="item.abbr" :label="item.name" :filter="filter" @onFilter="onFilter"/>
                <FilterButton v-for="item in data.nonCompetitionEvents" :key="item.id" :name="item.abbr" :label="item.title" :filter="filter" @onFilter="onFilter"/>
                <FilterButton name="Support" label="Support roles" :filter="filter" @onFilter="onFilter"/>
            </div>
            <div class="last">
                <ElSwitch v-model="byweapon" active-text="By Weapon" inactive-text="Individual" />
            </div>
        </div>
        <BasicParticipantList v-if="!byweapon" :dataList="dataList" :sorter="sorter" @on-edit="(e) => $emit('onEdit', e)" @on-select="(e) => $emit('onSelect', e)" @on-sort="onSort"/>
        <TeamParticipantList v-if="byweapon" :dataList="dataList" :filter="filter" @on-edit="(e) => $emit('onEdit', e)" @on-select="(e) => $emit('onSelect', e)"/>
    </div>
</template>