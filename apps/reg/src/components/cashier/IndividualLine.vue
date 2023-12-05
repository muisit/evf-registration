<script lang="ts" setup>
import { ref } from 'vue';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import type { FencerPayment } from './lib/payments';
import type { Registration } from '../../../../common/api/schemas/registration';
import { format_currency } from '../../../../common/functions';
const props = defineProps<{
    fencer:FencerPayment;
}>();
const data = useDataStore();
const auth = useAuthStore();

function wasPaid()
{
    if (auth.isHod()) {
        return props.fencer.paidToHod;
    }
    else if(auth.canCashier(data.currentEvent.id)) {
        return props.fencer.paidToOrg;
    }
    return false;
}

function wasReceived()
{
    return auth.isHod() && props.fencer.paidToOrg;
}

const isExpanded = ref(false);
function expand()
{
    isExpanded.value = !isExpanded.value;
}

function paymentState()
{
    let firstState = props.fencer.registrations[0].state || '';
    props.fencer.registrations.map((reg:Registration) => {
        if (reg.state != firstState) {
            firstState = '';
        }
    });
    return firstState;
}

function isDisabled()
{
    if (auth.isHod() && !['G'].includes(props.fencer.payment)) return true;
    else if (!['I','G'].includes(props.fencer.payment)) return true;
    return !(paymentState() == 'saved' || paymentState() == '');
}

function isVisible()
{
    return ['I', 'G'].includes(props.fencer.payment);
}

function getCompetitions()
{
    let comps:string[] = [];
    props.fencer.registrations.map((reg:Registration) => {
        let se = data.sideEventsById['s' + reg.sideEventId];
        if (se) {
            comps.push(se.abbr || '');
        }
    });
    return comps.join(', ');
}

function getCosts()
{
    let baseFee = 0.0;
    let total = 0.0;
    props.fencer.registrations.map((reg:Registration) => {
        let se = data.sideEventsById['s' + reg.sideEventId];
        if (se) {
            if (se.competition) {
                baseFee = (data.currentEvent.bank?.baseFee || 0.0);
                total += (data.currentEvent.bank?.competitionFee || 0.0);
            }
            else {
                total += se.costs;
            }
        }
    });
    return total + baseFee;
}

function markPayment(state:any)
{
    if (!isDisabled()) {
        let paymentTypes= ['I','G'];
        if (auth.isHod()) {
            paymentTypes = ['G'];
        }
        data.markPayments(
            props.fencer.registrations.filter((reg:Registration) => paymentTypes.includes(reg.payment || '')),
            auth.isHodFor() ? (state ? true : false) : null,
            auth.canCashier() ? (state ? true : false) : null);
    }
}


import IndividualDetail from './IndividualDetail.vue';
import { ElCheckbox, ElIcon } from 'element-plus';
import { Select, CloseBold, Upload, ArrowRight, ArrowDown, CircleCheck } from '@element-plus/icons-vue';
</script>
<template>
    <tr>
        <td>{{ props.fencer.fencer.lastName }}</td>
        <td>{{ props.fencer.fencer.firstName }}</td>
        <td>{{ data.currentEvent.bank?.symbol }} {{ format_currency(getCosts()) }}</td>
        <td>{{ getCompetitions() }}</td>
        <td>
            <span v-if="props.fencer.payment == 'G'">Group</span>
            <span v-if="props.fencer.payment == 'I'">Individual</span>
            <span v-if="props.fencer.payment == 'O'">Organisation</span>
            <span v-if="props.fencer.payment == 'E'">EVF</span>
            <span v-if="props.fencer.payment == 'M'">Mixed</span>
        </td>
        <td>
            <ElCheckbox :model-value="wasPaid()" @update:model-value="(e) => markPayment(e)" :disabled="isDisabled()" v-if="isVisible()"/>
        </td>
        <td>
            <ElIcon size="large" v-if="wasReceived()">
                <CircleCheck/>
            </ElIcon>
        </td>
        <td>
            <ElIcon size="large" @click="expand">
                <ArrowRight v-if="!isExpanded"/>
                <ArrowDown v-else/>
            </ElIcon>
        </td>
        <td :class="{'state-icons':true, 'state-error': props.fencer.registrations[0].state == 'error', 'state-upload': props.fencer.registrations[0].state == 'saving', 'state-ok': props.fencer.registrations[0].state == 'saved'}">
            <ElIcon>
                <CloseBold v-if="paymentState() == 'error'" />
                <Select v-if="paymentState() == 'saved'"/>
                <Upload v-if="paymentState() == 'saving'"/>
            </ElIcon>
        </td>
    </tr>
    <tr v-if="isExpanded" class="nostripes">
        <td colspan="3">
            <IndividualDetail :fencer="props.fencer"/>
        </td>
    </tr>
</template>