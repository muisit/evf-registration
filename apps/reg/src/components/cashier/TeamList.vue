<script lang="ts" setup>
import { Fencer } from '../../../../common/api/schemas/fencer';
import { Registration } from '../../../../common/api/schemas/registration';
import { Team } from './lib/payments';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { allowMoreTeams } from '../../../../common/lib/event';

const data = useDataStore();
const auth = useAuthStore();

function getSortedTeams()
{
    let teams = {};
    data.forEachRegistrationDo((fencer:Fencer, reg:Registration) => {
        if (reg.team) {
            let teamkey = 's' + reg.sideEventId + reg.team;
            if (!teams[teamkey]) {
                teams[teamkey] = {
                    name: reg.team,
                    sideEvent: data.sideEventsById['s' + reg.sideEventId],
                    fencers: [],
                    registrations: [],
                    paidToHod: true,
                    paidToOrg: true
                };
            }
            teams[teamkey].fencers.push(fencer);
            teams[teamkey].registrations.push(reg);
            if (reg.paidHod != 'Y') {
                teams[teamkey].paidToHod = false;
            }
            if (reg.paid != 'Y') {
                teams[teamkey].paidToOrg = false;
            }
        }
    });
    let teamArray:Team[] = [];
    Object.keys(teams).map((key:string) => {
        teamArray.push(teams[key]);
    });
    return teamArray.sort((a, b) => {
        // sort by event
        if (a.sideEvent && b.sideEvent && a.sideEvent.id != b.sideEvent.id) {
            return a.sideEvent.title > b.sideEvent.title ? 1 : -1;
        }
        // then by category
        if (a.sideEvent && b.sideEvent && a.sideEvent.competition && b.sideEvent.competition
            && a.sideEvent?.competition.category.name != b.sideEvent.competition.category.name
        ) {
            return a.sideEvent.competition.category.name > b.sideEvent.competition.category.name ? 1 : -1;
        }

        // then by team name
        if (a.name != b.name) {
            return a.name > b.name ? 1 : -1;
        }
        return 0;
    });
}

function markPayment(team:Team, state)
{
    data.markPayments(
        team.registrations,
        auth.isHodFor() ? (state ? true : false) : null,
        auth.canCashier() ? (state ? true : false) : null);
}

import TeamLine from './TeamLine.vue';
</script>
<template>
    <div class="team-list" v-if="getSortedTeams().length > 0">
        <table class="team-list">
            <thead>
                <tr>
                    <th>Team Competition</th>
                    <th v-if="allowMoreTeams(data.currentEvent)">Team</th>
                    <th>Fee</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <TeamLine v-for="(team,i) in getSortedTeams()" :key="i" :team="team" @on-update="(e) => markPayment(team, e)"/>
            </tbody>
        </table>
    </div>
</template>