<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { is_valid } from '../../../../../common/functions';
import type { Fencer } from '../../../../../common/api/schemas/fencer';
import type { Registration } from '../../../../../common/api/schemas/registration';
import { sortAndFilterFencers } from '../lib/sortAndFilterFencers';
import { useDataStore } from '../../../stores/data';
const emits = defineEmits(['onEdit', 'onSelect', 'onSort']);
const props = defineProps<{
    dataList:Fencer[];
    sorter:string[];
}>();

const data = useDataStore();

import SortableHeader from './SortableHeader.vue';
import ParticipantTable from './ParticipantTable.vue';
import { ElIcon } from 'element-plus';
import { Edit, Trophy } from '@element-plus/icons-vue';
</script>
<template>
    <table class="style-stripes">
        <SortableHeader :sorter="props.sorter" @onSort="(e) => $emit('onSort',e)" :sortable="true"/>
        <ParticipantTable :dataList="dataList" @onEdit="(e) => $emit('onEdit', e)" @onSelect="(e) => $emit('onSelect', e)" />
    </table>
</template>