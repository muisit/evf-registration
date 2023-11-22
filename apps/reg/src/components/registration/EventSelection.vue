<script lang="ts" setup>
import { ref } from 'vue';
import { Fencer } from '../../../../common/api/schemas/fencer';
import { SideEvent } from '../../../../common/api/schemas/sideevent';
import { useDataStore } from '../../stores/data';
import { selectEventsForFencer } from './lib/selectEventsForFencer';
import { is_valid } from '../../../../common/functions';
const props = defineProps<{
    fencer: Fencer;
    teams: string[];
    payments: string;
}>();

const data = useDataStore();

function availableEvents()
{
    return selectEventsForFencer(props.fencer).filter((event:SideEvent) => {
        if (!(event.isAthleteEvent || event.isNonCompetitionEvent || event.isRegistered)) return false;

        // if we are organisation, allow selecting the side-events, but not the competitions
        if(!is_valid(data.currentCountry.id) && !event.isNonCompetitionEvent) return false;

        return true;        
    });
}

function saveRegistration(event:SideEvent, state:any)
{
    console.log('save registration', event, state);
    if (state) {
        if (event.isAthleteEvent && event.isTeamEvent) {
            data.saveRegistration(props.fencer, event, event.defaultRole || null, state, props.payments);
        }
        else {
            data.saveRegistration(props.fencer, event, event.defaultRole || null, null, props.payments);
        }
    }
    else {
        data.deleteRegistration(props.fencer, event, null);
    }
}

function isRegistered(event:SideEvent)
{
    if (props.fencer.registrations) {
        for(let i = 0;i < props.fencer.registrations.length; i++) {
            if (props.fencer.registrations[i].sideEventId == event.id) {
                return props.fencer.registrations[i];
            }
        }
    }
    return null;
}

import SelectableEvent from './SelectableEvent.vue';
</script>
<template>
    <div class="event-selection">
        <h3>Competitions and Side Events</h3>
        <table class='fencer-select-events'>
            <SelectableEvent
                v-for="event in availableEvents()" :key="event.id"
                :event="event"
                :registration="isRegistered(event)"
                :teams="props.teams"
                @on-update="(e) => saveRegistration(event, e)"
            />
        </table>
    </div>
</template>
