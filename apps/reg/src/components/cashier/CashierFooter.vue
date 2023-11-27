<script lang="ts" setup>
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { format_currency } from '../../../../common/functions';
import { Registration } from '../../../../common/api/schemas/registration';
import { Fencer } from '../../../../common/api/schemas/fencer';
const data = useDataStore();
const auth = useAuthStore();

function addToGroupCosts(reg:Registration, costs:number, totals:any)
{
    if (reg.payment == 'G') {
        totals.wholegroup += costs;
        if (reg.paidHod == 'Y') {
            totals.receivedgroup += costs;
        }
        if (reg.paid == 'Y') {
            totals.ackgroup += costs;
        }
    }
    else if(reg.payment == 'I') {
        totals.wholeindividual += costs;
        // individual payments are never acknowledged by the HoD
        if (reg.paid == 'Y') {
            totals.ackindividual += costs;
        }
    }
    else if (reg.payment == 'O') {
        totals.wholeorg += costs;
        // organisation costs are never checked or acknowledged
    }
    else if (reg.payment == 'E') {
        totals.wholeevf += costs;
        // EVF costs are never checked or acknowledged
    }
    return totals;
}

function totals()
{
    let totals = {
        'wholegroup': 0,
        'pendinggroup': 0,
        'receivedgroup': 0,
        'ackgroup': 0,
        'remaingroup': 0,
        'wholeindividual': 0,
        'ackindividual': 0,
        'remainindividual': 0,
        'wholeorg': 0,
        'wholeevf': 0
    };

    let baseFee = data.currentEvent.bank?.baseFee || 0.0;
    let competitionFee = data.currentEvent.bank?.competitionFee || 0.0;
    let teamsPaid = {};
    let fencersPaid = {};

    data.forEachRegistrationDo((fencer:Fencer, reg:Registration) => {
        let fid = 'f' + fencer.id;
        let hasPaidBaseFee = fencersPaid[fid] || false;

        let sideEvent = data.sideEventsById['s' + reg.sideEventId];
        if (sideEvent && sideEvent.competition) {
            if (sideEvent.competition.category && sideEvent.competition.category.type == 'T') {
                var teamname = 'p' + sideEvent.id + reg.team;
                if (!teamsPaid[teamname]) {
                    // there is no baseFee + competitionFee for team events
                    totals = addToGroupCosts(reg, competitionFee, totals);
                    teamsPaid[teamname] = true;
                }
            }
            else {
                let thesecosts = competitionFee;
                if (!hasPaidBaseFee) {
                    thesecosts += baseFee;
                    fencersPaid[fid] = true;
                }
                totals = addToGroupCosts(reg, thesecosts, totals);
            }
        }
        else if (sideEvent && sideEvent.costs > 0) {
            totals = addToGroupCosts(reg, sideEvent.costs, totals);
        }
    });

    totals.pendinggroup = totals.wholegroup - totals.receivedgroup;
    totals.remaingroup = totals.wholegroup - totals.ackgroup;
    totals.remainindividual = totals.wholeindividual - totals.ackindividual;
    return totals;
}

function showHodData()
{
    return !auth.canCashier();
}
</script>
<template>
    <div class="cashier-footer">
        <table class="payment-details">
            <tbody v-if="totals()['wholegroup'] > 0">
                <tr>
                    <td colspan="2" class="cashier-detail-header">Group Costs</td>
                </tr>
                <tr>
                    <td class='label'>Total</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['wholegroup']) }}</td>
                </tr>
                <tr v-if="showHodData()">
                    <td class='label'>Received from participants</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['receivedgroup']) }}</td>
                </tr>
                <tr v-if="showHodData()">
                    <td class='label'>Pending from participants</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['pendinggroup']) }}</td>
                </tr>
                <tr>
                    <td class='label'>Received by organisation</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['ackgroup']) }}</td>
                </tr>
                <tr>
                    <td class='label'>Transferrable to organisation</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['remaingroup']) }}</td>
                </tr>
            </tbody>
            <tbody v-if="totals()['wholeindividual'] > 0">
                <tr>
                    <td colspan="2" class="cashier-detail-header">Individual Costs</td>
                </tr>
                <tr>
                    <td class='label'>Total</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['wholeindividual']) }}</td>
                </tr>
                <tr>
                    <td class='label'>Received by organisation</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['ackindividual']) }}</td>
                </tr>
                <tr>
                    <td class='label'>Transferrable to organisation</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['remainindividual']) }}</td>
                </tr>
            </tbody>
            <tbody v-if="totals()['wholeorg'] > 0">
                <tr>
                    <td colspan="2" class="cashier-detail-header">Organisation Costs</td>
                </tr>
                <tr>
                    <td class='label'>Total</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['wholeorg']) }}</td>
                </tr>
            </tbody>
            <tbody v-if="totals()['wholeevf'] > 0">
                <tr>
                    <td colspan="2" class="cashier-detail-header">EVF Costs</td>
                </tr>
                <tr>
                    <td class='label'>Total</td>
                    <td>{{  data.currentEvent.bank?.symbol }} {{ format_currency(totals()['wholeevf']) }}</td>
                </tr>
            </tbody>
        </table>
        <table className='payment-details'>
            <tbody>
                <tr>
                    <td colspan='2' class='cashier-detail-header'>Payment Details</td>
                </tr>
                <tr>
                    <td class='label'>Bank Account</td>
                    <td>{{ data.currentEvent.bank?.iban }}</td>
                </tr>
                <tr>
                    <td class='label'>Reference</td>
                    <td>{{ data.currentEvent.bank?.reference }}</td>
                </tr>
                <tr>
                    <td class='label'>Account Name</td>
                    <td>{{ data.currentEvent.bank?.account }}</td>
                </tr>
                <tr>
                    <td class='label'>Account Address</td>
                    <td>{{ data.currentEvent.bank?.address }}</td>
                </tr>
                <tr>
                    <td class='label'>Bank</td>
                    <td>{{ data.currentEvent.bank?.bank }}</td>
                </tr>
                <tr>
                    <td class='label'>SWIFT/BIC</td>
                    <td>{{ data.currentEvent.bank?.swift }}</td>
                </tr>
            </tbody>
        </table>        
    </div>
</template>