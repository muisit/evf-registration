<script lang="ts" setup>
import type { Fencer } from '../../../../common/api/schemas/fencer';
import type { SideEvent } from '../../../../common/api/schemas/sideevent';
import { useDataStore } from '../../stores/data';
import type { StringKeyedStringList } from '../../../../common/types';
const props = defineProps<{
    fencer: Fencer;
    teams: StringKeyedStringList;
    payments: string;
    availableEvents: SideEvent[];
}>();

const data = useDataStore();

function saveRegistration(event:SideEvent, state:any)
{
    // state is null, the empty string, true/false or a team name
    if (state) {
        if (event.isAthleteEvent && event.isTeamEvent) {
            // state is the team-name
            // always mark this as a group-payment, if we have a choice
            data.saveRegistration(props.fencer, event, null, state, data.currentEvent.payments == 'all' ? 'G' : props.payments);
        }
        else {
            // individual tournament, no team name
            data.saveRegistration(props.fencer, event, null, null, props.payments);
        }
    }
    else {
        data.removeRegistration(props.fencer, event, null);
    }
}

function getTeamsForEvent(event:SideEvent):string[]
{
    if (event.competition?.weapon?.name) {
        if (props.teams[event.competition.weapon.name || '']) {
            return props.teams[event.competition.weapon.name || ''];
        }
    }
    return [];
}

import SelectableEvent from './SelectableEvent.vue';
</script>
<template>
    <div class="event-selection">
        <h3>Competitions and Side Events</h3>
        <table class='fencer-select-events'>
            <SelectableEvent
                v-for="event in props.availableEvents" :key="event.id"
                :event="event"
                :fencer="props.fencer"
                :teams="getTeamsForEvent(event)"
                @update="(e) => saveRegistration(event, e)"
            />
        </table>
    </div>
</template>
