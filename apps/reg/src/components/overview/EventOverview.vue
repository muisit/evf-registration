<script lang="ts" setup>
import type { CountrySchema } from '../../../../common/api/schemas/country';
import type { OverviewObject } from '../../../../common/api/schemas/overviewline';
import { useDataStore } from '../../stores/data';
const emits = defineEmits(['changeTab']);

const data = useDataStore();

function getLines(country:CountrySchema)
{
    var ckey = 'c' + country.id;
    if (data.overviewPerCountry && data.overviewPerCountry[ckey]) { 
        return data.overviewPerCountry[ckey];
    }
    return {country:country, events:{}};
}

function getTotalLines()
{
    var retval:OverviewObject = {country: { id: 0, name: 'Total', abbr: '', path: ''}, events: {}};
    if (data.overviewPerCountry) {
        data.sideEvents.forEach((se) => {
            calculateTotalForEvent(retval, 's' + se.id);
        });
        calculateTotalForEvent(retval, 'ssup');
    }
    return retval;
}

function calculateTotalForEvent(countObject: OverviewObject, skey: string)
{
    var totalParticipants = 0;
    var totalTeams = 0;
    data.countries.forEach((country) => {
        var ckey = 'c' + country.id;
        if (data.overviewPerCountry[ckey] && data.overviewPerCountry[ckey].events[skey]) {

            totalParticipants += data.overviewPerCountry[ckey].events[skey].participants;
            totalTeams += data.overviewPerCountry[ckey].events[skey].teams;
        }
    });
    countObject.events[skey] = { sideEvent: null, participants: totalParticipants, teams: totalTeams};
}

import CountryHeader from './CountryHeader.vue';
import CountryLine from './CountryLine.vue';
</script>
<template>
    <div class="country-overview">
      <h5 class="block-title">Registrations per Country</h5>
      <table class='style-stripes'>
        <CountryHeader />
        <tbody>
            <CountryLine :line="getTotalLines()" :is-total="true"/>
            <CountryLine v-for="country in data.countries" :key="country.id" :line="getLines(country)" :country="country" :is-total="false" @change-tab="(e) => $emit('changeTab', e)"/>
        </tbody>
      </table>
    </div>
</template>