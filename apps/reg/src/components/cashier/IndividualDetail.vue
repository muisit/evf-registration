<script lang="ts" setup>
import { Registration } from "../../../../common/api/schemas/registration";
import { FencerPayment } from "./lib/payments";
import { format_currency } from "../../../../common/functions";
import { useDataStore } from "../../stores/data";
import { useAuthStore } from "../../../../common/stores/auth";
const props = defineProps<{
    fencer:FencerPayment;
}>();
const emits = defineEmits(['onUpdate']);

const auth = useAuthStore();
const data = useDataStore();
function getSideEvent(reg:Registration)
{
    let se = data.sideEventsById['s' + reg.sideEventId];
    if (se) {
        return se.title;
    }
    return 'unknown';
}

function getFee(reg:Registration, index:number)
{
    let retval = 0.0;
    let se = data.sideEventsById['s' + reg.sideEventId];
    if (se) {
        if (se.competition) {
            retval = data.currentEvent.bank?.competitionFee || 0.0;
            if (index == 0) {
                retval += (data.currentEvent.bank?.baseFee || 0.0);
            }
        }
        else if(se.costs) {
            retval = se.costs;
        }
    }
    return retval;
}

function wasPaid(reg:Registration)
{
    if (auth.isHod()) {
        return reg.paidHod == 'Y';
    }
    else if(auth.canCashier(data.currentEvent.id)) {
        return reg.paid == 'Y';
    }
    return false;
}

function wasReceived(reg:Registration)
{
    return auth.isHod() && reg.paid == 'Y';
}

function isDisabled(reg:Registration)
{
    if (auth.isHod() &&  !['G'].includes(reg.payment || '')) return true;
    else if (!['I','G'].includes(reg.payment || '')) return true;
    return !(reg.state == 'saved' || reg.state == '' || !reg.state);
}
function isVisible()
{
    return ['I', 'G'].includes(props.fencer.payment);
}

function markSimplePayment(reg:Registration, state) {
    if (!isDisabled(reg)) {
        data.markPayments(
            [reg],
            auth.isHodFor() ? (state ? true : false) : null,
            auth.canCashier() ? (state ? true : false) : null);
    }
}

import { ElIcon, ElCheckbox } from 'element-plus';
import { CloseBold, Select, Upload, CircleCheck } from '@element-plus/icons-vue';
</script>
<template>
    <div class="individual-details">
        <table class="nostripes">
            <tbody>
                <tr v-for="(reg,i) in props.fencer.registrations" :key="reg.id">
                    <td>{{ getSideEvent(reg) }}</td>
                    <td>{{ data.currentEvent.bank?.symbol }} {{ format_currency(getFee(reg, i)) }}</td>
                    <td>
                        <span v-if="reg.payment == 'G'">Group</span>
                        <span v-if="reg.payment == 'I'">Individual</span>
                        <span v-if="reg.payment == 'O'">Organisation</span>
                        <span v-if="reg.payment == 'E'">EVF</span>
                    </td>
                    <td>
                        <ElCheckbox :model-value="wasPaid(reg)" @update:model-value="(e) => markSimplePayment(reg, e)" :disabled="isDisabled(reg)" v-if="isVisible()"/>
                    </td>
                    <td v-if="auth.isHod()">
                        <ElIcon size="large" v-if="wasReceived(reg)">
                            <CircleCheck />
                        </ElIcon>
                    </td>
                    <td :class="{'state-icons':true, 'state-error': reg.state == 'error', 'state-upload': reg.state == 'saving', 'state-ok': reg.state == 'saved'}">
                        <ElIcon>
                            <CloseBold v-if="reg.state == 'error'" />
                            <Select v-if="reg.state == 'saved'"/>
                            <Upload v-if="reg.state == 'saving'"/>
                        </ElIcon>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>