<script lang="ts" setup>
import type { Event } from '../../../../common/api/schemas/event';
import type { SideEvent } from '../../../../common/api/schemas/sideevent';
import { useDataStore } from '../../stores/data';
import { downloadCSV, downloadXML } from '../../../../common/api/event/download';
const data = useDataStore();

function downloadcsv(event:SideEvent)
{
    return downloadCSV(event);
}
function downloadxml(event:SideEvent)
{
    return downloadXML(event);
}

import EventLine from './EventLine.vue';
</script>
<template>
    <table>
        <thead>
            <tr>
                <th>Competition/Event</th>
                <th>CSV</th>
                <th>XML</th>
            </tr>
        </thead>
        <tbody>
            <EventLine v-for="event in data.competitionEvents" :key="event.id" :event="event" @downloadcsv="(e) => downloadcsv(e)"  @downloadxml="(e) => downloadxml(e)"/>
            <EventLine v-for="event in data.nonCompetitionEvents" :key="event.id" :event="event" @downloadcsv="(e) => downloadcsv(e)"  @downloadxml="(e) => downloadxml(e)"/>
        </tbody>
    </table>
</template>