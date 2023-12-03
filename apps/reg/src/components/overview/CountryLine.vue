<script lang="ts" setup>
import type { CountrySchema } from '../../../../common/api/schemas/country';
import type { OverviewObject } from '../../../../common/api/schemas/overviewline';
import { is_valid } from '../../../../common/functions';
import { useAuthStore } from '../../../../common/stores/auth';
const props = defineProps<{
    line:OverviewObject;
    isTotal: boolean;
    country?: CountrySchema;
}>();
import { useDataStore } from '../../stores/data';
const emits = defineEmits(['changeTab']);

const data = useDataStore();
const auth = useAuthStore();

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

function selectForRegistration()
{
    if (auth.isOrganisation() && !props.isTotal) {
        data.setCountry(props.country?.id || 0);
        emits('changeTab', 'registration');
    }
}
</script>
<template>
    <tr v-if="line.country" :class="{totalHeader:props.isTotal}" @click="selectForRegistration">
        <td class="text-left">{{ line.country.name }}</td>
        <td class="text-right" v-for="se in data.competitionEvents" :key="se.id">{{ outputCount(se) }}</td>
        <td class="text-right"><b>{{ calculateTotal() }}</b></td>
        <td class="text-right" v-for="se in data.nonCompetitionEvents" :key="se.id">{{ outputCount(se) }}</td>
        <td class="text-right">{{ outputCount({id: 'sup'}) }}</td>
    </tr>
</template>