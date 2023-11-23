<script lang="ts" setup>
import type { Fencer } from '../../../../../common/api/schemas/fencer';
import { WeaponSchema } from '../../../../../common/api/schemas/weapon';
import { useDataStore } from '../../../stores/data';
const emits = defineEmits(['onEdit', 'onSelect']);
const props = defineProps<{
    dataList:Fencer[];
    filter:string[];
}>();

const data = useDataStore();

function separateFencersByWeapon()
{
    let byWeapon = {"other": []};
    props.dataList.map((fencer:Fencer) => {
        if (fencer.registrations) {
            fencer.registrations.map((reg) => {
                var sideEvent = data.sideEventsById['s' + reg.sideEventId] || null;
                if (!sideEvent || !sideEvent.competition || !sideEvent.competition.weapon) {
                    byWeapon['other'].push(reg);
                }
                else {
                    let wpn = sideEvent.competition.weapon;
                    if(props.filter.length == 0 || props.filter.includes(wpn.abbr)) {
                        if (!byWeapon[wpn.abbr]) {
                            byWeapon[wpn.abbr] = [];
                        }
                        byWeapon[wpn.abbr].push(reg);
                    }
                }
            });
        }
    });

    if (props.filter && props.filter.length && !props.filter.includes('Support')) {
        delete byWeapon['other'];
    }

    return Object.keys(byWeapon).sort().map((key) => {
        let weapon:WeaponSchema|null = null;
        data.weapons.map((wpn:WeaponSchema) => {
            if (wpn.abbr == key) {
                weapon = wpn;
            }
        });

        return {
            name: key,
            weapon: weapon || {id: 0, name:'Support Roles', abbr:'', gender:''},
            registrations: byWeapon[key]
        };
    });
}

import TeamsByWeapon from './TeamsByWeapon.vue';
import { ElIcon } from 'element-plus';
import { Edit, Trophy } from '@element-plus/icons-vue';
</script>
<template>
    <table class="registrations-per-weapon style-stripes">
        <TeamsByWeapon v-for="obj in separateFencersByWeapon()" :key="obj.name" :registrations="obj.registrations" :weapon="obj.weapon" @on-edit="(e) => $emit('onEdit', e)" @on-select="(e) => $emit('onSelect', e)" />
    </table>
</template>