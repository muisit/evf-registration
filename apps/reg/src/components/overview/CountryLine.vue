<script lang="ts" setup>
import { is_valid } from '../../../../common/functions';
const props = defineProps<{
    line:any;
    isTotal: boolean;
}>();
import { useDataStore } from '../../stores/data';
const data = useDataStore();

function calculateTotal()
{
    var tot = 0;
    Object.keys(props.line.events).map((skey) => {
        var cnt = props.line.events[skey];
        var se = data.sideEventsById[skey];
        if (is_valid(se) && is_valid(se.competition)) {
            if (cnt.teams > 0) {
                tot += cnt.teams;
            }
            else {
                tot += cnt.participants;
            }
        }
    });
    return tot;
}

function outputCount(sideEvent:any)
{
    var key = 's' + sideEvent.id;
    if (props.line.events[key]) {
        if (props.line.events[key].participants < 1) {
            return '';
        }
        if (props.line.events[key].teams > 0) {
            return props.line.events[key].participants + ' (' + props.line.events[key].teams + ')';
        }
        return props.line.events[key].participants;
    }
    else {
        return '';
    }
}
</script>
<template>
    <tr v-if="line.country" :class="{totalHeader:isTotal}">
        <td class="text-left">{{ line.country.name }}</td>
        <td class="text-right" v-for="se in data.competitionEvents" :key="se.id">{{ outputCount(se) }}</td>
        <td class="text-right"><b>{{ calculateTotal() }}</b></td>
        <td class="text-right" v-for="se in data.nonCompetitionEvents" :key="se.id">{{ outputCount(se) }}</td>
        <td class="text-right">{{ outputCount({id: 'sup'}) }}</td>
    </tr>
</template>