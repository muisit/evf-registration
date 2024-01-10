<script lang="ts" setup>
import type { Fencer } from '../../../../../common/api/schemas/fencer';
import { defaultSideEvent } from '../../../../../common/api/schemas/sideevent';
import type { SideEvent } from '../../../../../common/api/schemas/sideevent';
import type { Registration } from '../../../../../common/api/schemas/registration';
import { useDataStore } from '../../../stores/data';
const emits = defineEmits(['onEdit', 'onSelect']);
const props = defineProps<{
    dataList:Fencer[];
    filter:string[];
}>();

const data = useDataStore();

interface CompetitionRegistrations {
    sideEvent: SideEvent|null;
    registrations: Registration[];
}

interface FencerByCompetition {
    [key:string]: CompetitionRegistrations;
}

function separateFencersByCompetition()
{
    let byWeapon:FencerByCompetition = {};
    byWeapon['other'] = {sideEvent: null, registrations: []};
    props.dataList.map((fencer:Fencer) => {
        if (fencer.registrations) {
            fencer.registrations.map((reg) => {
                var sideEvent = data.sideEventsById['s' + reg.sideEventId] || null;
                if (!sideEvent || !sideEvent.competition || !sideEvent.competition.weapon) {
                    byWeapon['other'].registrations.push(reg);
                }
                else {
                    let key = 's' + sideEvent.id;
                    if(props.filter.length == 0 || props.filter.includes(key)) {
                        if (!byWeapon[key || '']) {
                            byWeapon[key || ''] = {sideEvent: sideEvent, registrations: []};
                        }
                        byWeapon[key || ''].registrations.push(reg);
                    }
                }
            });
        }
    });

    if (props.filter && props.filter.length && !props.filter.includes('Support')) {
        delete byWeapon['other'];
    }

    return Object.keys(byWeapon).sort((as, bs) => {
        let a:SideEvent|null = byWeapon[as].sideEvent;
        let b:SideEvent|null = byWeapon[bs].sideEvent;
        if (a && a.competition && a.competition.weapon && b && b.competition && b.competition.weapon) {
            // first sort on weapon
            if (a.competition.weapon.id == b.competition.weapon.id) {
                // categories cannot be the same
                let cata = a.competition.category?.abbr || '';
                let catb = b.competition.category?.abbr || '';
                return cata > catb ? 1 : -1;
            }
            else {
                let wa = a.competition.weapon?.name || '';
                let wb = b.competition.weapon?.name || '';
                return wa > wb ? 1 : -1;
            }
        }
        else if(a && !b) {
            return -1;
        }
        else if (b && !a) {
            return 1;
        }
        return 0;
    }).map((key) => {
        return {
            name: key == 'other' ? 'Support' : byWeapon[key].sideEvent?.title,
            event: byWeapon[key].sideEvent || defaultSideEvent({title:'Support Roles'}),
            registrations: byWeapon[key].registrations
        };
    });
}

import TeamsByCompetition from './TeamsByCompetition.vue';
import { ElIcon } from 'element-plus';
import { Edit, Trophy } from '@element-plus/icons-vue';
</script>
<template>
    <table class="registrations-per-weapon style-stripes">
        <TeamsByCompetition v-for="obj in separateFencersByCompetition()" :key="obj.name" :registrations="obj.registrations" :event="obj.event" @on-edit="(e) => $emit('onEdit', e)" @on-select="(e) => $emit('onSelect', e)" />
    </table>
</template>