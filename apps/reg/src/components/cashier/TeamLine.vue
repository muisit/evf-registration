<script lang="ts" setup>
import { ref } from 'vue';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { Team } from './lib/team';
import { format_currency } from '../../../../common/functions';
import { allowMoreTeams } from '../../../../common/lib/event';
const props = defineProps<{
    team:Team;
}>();
const emits = defineEmits(['onUpdate']);
const data = useDataStore();
const auth = useAuthStore();

function wasPaid()
{
    if (auth.isHod()) {
        return props.team.paidToHod;
    }
    else if(auth.canCashier(data.currentEvent.id)) {
        return props.team.paidToOrg;
    }
    return false;
}

function wasReceived()
{
    if (auth.isHod()) {
        return props.team.paidToOrg;
    }
    return false;
}

const isExpanded = ref(false);
function expand()
{
    isExpanded.value = !isExpanded.value;
}

function paymentState()
{
    return props.team.registrations[0].state || '';
}

function isDisabled()
{
    return !(paymentState() == 'saved' || paymentState() == '');
}

import { ElCheckbox, ElIcon } from 'element-plus';
import { Select, CloseBold, Upload, ArrowRight, ArrowDown, CircleCheck } from '@element-plus/icons-vue';
</script>
<template>
    <tr>
        <td>{{ props.team.sideEvent.title }}</td>
        <td v-if="allowMoreTeams(data.currentEvent)">{{ props.team.name }}</td>
        <td>{{ data.currentEvent.bank?.symbol }} {{ format_currency(data.currentEvent.bank?.competitionFee) }}</td>
        <td><ElCheckbox :model-value="wasPaid()" @update:model-value="(e) => $emit('onUpdate', e)" :disabled="isDisabled()"/></td>
        <td>
            <ElIcon size="large" @click="expand">
                <ArrowRight v-if="!isExpanded"/>
                <ArrowDown v-else/>
            </ElIcon>
        </td>
        <td>
            <ElIcon size="large" v-if="wasReceived() && auth.isHod()">
                <CircleCheck/>
            </ElIcon>
        </td>
        <td :class="{'state-icons':true, 'state-error': props.team.registrations[0].state == 'error', 'state-upload': props.team.registrations[0].state == 'saving', 'state-ok': props.team.registrations[0].state == 'saved'}">
            <ElIcon>
                <CloseBold v-if="paymentState() == 'error'" />
                <Select v-if="paymentState() == 'saved'"/>
                <Upload v-if="paymentState() == 'saving'"/>
            </ElIcon>
        </td>
    </tr>
    <tr v-if="isExpanded">
        <td colspan="3">
            <div v-for="fencer in props.team.fencers" class="team-constituants">
                {{ fencer.fullName }}
            </div>
        </td>
    </tr>
</template>