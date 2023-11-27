<script lang="ts" setup>
import { Fencer } from '../../../../common/api/schemas/fencer';
import { Registration } from '../../../../common/api/schemas/registration';
import { FencerPayment } from './lib/payments';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';

const data = useDataStore();
const auth = useAuthStore();

function getSortedIndividuals()
{
    let fencers = {};

    data.forEachRegistrationDo((fencer:Fencer, reg:Registration) => {       
        if (!reg.team && reg.sideEventId) {
            var sideEvent = data.sideEventsById['s' + reg.sideEventId];
            if (fencer && sideEvent && (sideEvent.competition || sideEvent.costs)) {
                var fid = 'f' + fencer.id;

                if (!fencers[fid]) {
                    fencers[fid] = { fencer: fencer, registrations: [], paidToHod: true, paidToOrg: true, payment: ''};
                }
                fencers[fid].registrations.push(reg);

                if (reg.paid != 'Y') {
                    fencers[fid].paidToOrg = false;
                }
                if (reg.paidHod != 'Y') {
                    fencers[fid].paidToHod = false;
                }
                if (fencers[fid].payment != '' && reg.payment != fencers[fid].payment) {
                    fencers[fid].payment = 'M'; // mixed
                }
                else {
                    fencers[fid].payment = reg.payment;
                }
            }
        }
    });
    let fencerArray:FencerPayment[] = [];
    Object.keys(fencers).map((key:string) => {
        let entry = fencers[key];
        entry.registrations.sort((a, b) => {
            let sa = data.sideEvents['s' + a.sideEventId];
            let sb = data.sideEvents['s' + b.sideEventId];
            if (!sa && !sb) return 0;
            if (!sa && sb) return 1;
            if (sa && !sb) return -1;
            return sa.title > sb.title ? 1 : -1;
        });
        fencerArray.push(entry);
    });
    return fencerArray.sort((a, b) => {
        // sort by fencer name, which ought to be unique within this list
        return a.fencer.fullName > b.fencer.fullName ? 1 : -1;
    });
}


import IndividualLine from './IndividualLine.vue';
</script>
<template>
    <div class="individual-list" v-if="getSortedIndividuals().length > 0">
        <table class="style-stripes">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>First name</th>                    
                    <th>Fee</th>
                    <th>Competitions</th>
                    <th>Payment</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <IndividualLine v-for="(fencer,i) in getSortedIndividuals()" :key="i" :fencer="fencer"/>
            </tbody>
        </table>
    </div>
</template>
