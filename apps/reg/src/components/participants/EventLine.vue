<script lang="ts" setup>
import type { SideEvent } from '../../../../common/api/schemas/sideevent';
const props = defineProps<{
    event:SideEvent;
}>();
const emits = defineEmits(['downloadcsv', 'downloadxml']);

function doDownloadCSV()
{
    emits('downloadcsv', props.event);
}

function doDownloadXML()
{
    emits('downloadxml', props.event);
}

import { ElIcon, ElButton } from 'element-plus';
import { List } from '@element-plus/icons-vue';
</script>
<template>
    <tr>
        <td>{{ props.event.title }}</td>
        <td><ElButton class="uppercase" @click="doDownloadCSV">csv</ElButton></td>
        <td><ElButton v-if="props.event.competition" class="uppercase" @click="doDownloadXML">xml</ElButton></td>
    </tr>
</template>