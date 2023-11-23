<script lang="ts" setup>
import { Registration } from '../../../../../common/api/schemas/registration';
import { Fencer } from '../../../../../common/api/schemas/fencer';
import { useDataStore } from '../../../stores/data';
import { WeaponSchema } from '../../../../../common/api/schemas/weapon';
const props = defineProps<{
    registrations: Registration[];
    weapon: WeaponSchema;
}>();
const emits = defineEmits(['onEdit', 'onSelect']);


function fencerData()
{
    let myregs = props.registrations.slice();
    const data = useDataStore();
    myregs.sort((a:Registration, b:Registration) => {
        // first sort by competition category
        let sa = data.sideEventsById['s' + a.sideEventId] || null;
        let sb = data.sideEventsById['s' + b.sideEventId] || null;
        if (sa && sb && sa.title != sb.title) {
            console.log('sorting on event title');
            return sa.title > sb.title ? 1 : -1;
        }

        if (a.team && !b.team) return -1;
        if (b.team && !a.team) return 1;
        if (a.team && b.team && a.team != b.team) return a.team > b.team ? 1 : -1;
        
        let fa = data.fencerData['f' + a.fencerId] || null;
        let fb = data.fencerData['f' + b.fencerId] || null;

        // fencer should always be found
        if (!fa && fb) return 1;
        if (!fb && fa) return -1;
        if (!fa && !fb) return 0;

        if (fa.lastName > fb.lastName) return 1;
        if (fb.lastName > fa.lastName) return -1;
        if (fa.firstName > fb.firstName) return 1;
        if (fb.firstName > fa.firstName) return -1;
        return 0;
    });
    return myregs.map((reg:Registration) => {
        let fencer = data.fencerData['f' + reg.fencerId];
        return fencer;
    }).filter((el) => el != null).filter((el, i, a) => i == a.indexOf(el));
}
import SortableHeader from './SortableHeader.vue';
import ParticipantTable from './ParticipantTable.vue';
</script>
<template>
    <SortableHeader :weapon="props.weapon" :sortable="false" :sorter="[]" />
    <ParticipantTable :dataList="fencerData()" :weapon="props.weapon"  @onEdit="(e) => $emit('onEdit', e)" @onSelect="(e) => $emit('onSelect', e)" />
    <tbody><tr class='filler'><td colspan="9"></td></tr></tbody>
</template>