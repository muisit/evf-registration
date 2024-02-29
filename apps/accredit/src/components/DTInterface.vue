<script lang="ts" setup>
import { ref, computed, watch } from 'vue';
import type { Ref } from 'vue';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import type { AccreditationStatistics } from '../../../common/api/schemas/accreditationstatistics';
import type { SideEvent } from '../../../common/api/schemas/sideevent';
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { Registration } from '../../../common/api/schemas/registration';
import type { StringKeyedIdList } from '../../../common/types';
import type { StatById, FencerDataById } from './lib/types';
import { accreditationstatistics } from '../../../common/api/accreditations/accreditationstatistics';
import { allRegistrations } from '../../../common/api/registrations/registrations';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
import { useDataStore } from '../stores/data';
import { useBroadcasterStore } from '../../../common/stores/broadcaster';

const props = defineProps<{
    visible:boolean;
}>();


const auth = useAuthStore();
const data = useDataStore();
const basic = useBasicStore();
const broadcaster = useBroadcasterStore();
const statList:Ref<StatById> = ref({});
const fencerList:Ref<FencerDataById> = ref({});
const registrationList:Ref<StringKeyedIdList> = ref({});
const selectedEvent:Ref<SideEvent|null> = ref(null);
const updatesReceived = ref(0);

watch(() => auth.credentials,
    (nw) => {
        if (auth.isDT()) {
            broadcaster.subscribeToDt((type:string, content:any) => {
                adjustInternalAdministration(type, content);

                // every now and then just reload all statistics
                if (updatesReceived.value > 5) {
                    accreditationstatistics().then((dt) => {
                        if (dt) {
                            dt.map((a:AccreditationStatistics) => {
                                statList.value['s' + a.eventId] = a;
                            });
                        }
                        updatesReceived.value = 0;
                    });
                }
            });
        }
        else {
            broadcaster.unsubscribe('dt');
        }
    },
    { immediate: true }
);

function adjustInternalAdministration(type: string, content:any)
{
    updatesReceived.value += 1;
    if (type == 'AccreditationHandoutEvent') {
        let fencer:Fencer = content;
        console.log('accreditation handout for ', fencer.id);
        let key = 'f' + fencer.id;
        if (!fencerList.value[key]) {
            console.log('adding new fencer');
            fencerList.value[key] = { fencer: fencer, checkin: [], checkout: []};
        }
        console.log('fencer registrations: ', fencer.registrations);
        fencerList.value[key].fencer = fencer;

        foreachFencerRegistration(fencer.id, (r:Registration, key:string) => {
            console.log('updating state for ', basic.sideEventsById[key].abbr, r.sideEventId, r.state);
            if (r.state == 'P') {
                statList.value[key].pending -= 1;
                statList.value[key].present += 1;
            }
            else if (r.state == 'A') {
                statList.value[key].pending -= 1;
                statList.value[key].cancelled += 1;
            }
            else {
                // assume a present is reverted to R (pending)
                // This may not be true, so we need to completely reload the statistics every now and then
                statList.value[key].present -= 1;
                statList.value[key].pending += 1;
            }
        });
    }
    else {
        let doc:AccreditationDocument = content;
        let key = 'f' + doc.fencerId;
        console.log('received ', type, ' for ', doc.fencerId, doc);
        if (fencerList.value[key]) {
            if (type == 'CheckinEvent') {
                console.log('checking event, adding ', doc.checkin);
                fencerList.value[key].checkin.push(doc.checkin || '');
                foreachFencerRegistration(doc.fencerId || 0, (r:Registration, skey:string) => {
                    console.log('updating checkin for ', basic.sideEventsById[skey].abbr, r.sideEventId, r.state);
                    statList.value[skey].checkin += 1;
                });
            }
            else if (type == 'CheckoutEvent') {
                console.log('checkout event, adding ', doc.checkout, ' removing ', doc.checkin);
                fencerList.value[key].checkin = fencerList.value[key].checkin.filter((v:string) => v != (doc.checkin || ''));
                fencerList.value[key].checkout.push(doc.checkout || '');
                foreachFencerRegistration(doc.fencerId || 0, (r:Registration, skey:string) => {
                    console.log('updating checkout for ', basic.sideEventsById[skey].abbr, r.sideEventId, r.state);
                    statList.value[skey].checkin -= 1;
                    statList.value[skey].checkout += 1;
                });
            }
        }
    }
}

function foreachFencerRegistration(fid:number, cb:Function)
{
    let key = 'f' + fid;
    if (fencerList.value[key]) {
        (fencerList.value[key].fencer.registrations || []).map((r:Registration) => {
            let key = 's' + r.sideEventId;
            if (statList.value[key]) {
                cb(r, key);
            }
        });
    }
}

const compListByDate = computed(() => {
    return basic.competitionEvents.sort((c1:SideEvent, c2:SideEvent) => {
        if (c1.starts != c2.starts) {
            return c1.starts > c2.starts ? 1 : -1;
        }
        return c1.title > c2.title ? 1 : -1;
    });
});

function failDispatcher(code:string, codeObject:Code)
{
    onDialogClose();
}

watch(() => props.visible,
    (nw) => {
        if (nw) {
            accreditationstatistics().then((dt) => {
                if (dt) {
                    dt.map((a:AccreditationStatistics) => {
                        statList.value['s' + a.eventId] = a;
                    });
                }
            });
            loadAllRegistrations();

            data.subtitle = "DT Event Overview";
            data.clearDispatchers();
            data.setDispatcher('fail', failDispatcher);
        }
    },
    { immediate: true }
)

function loadAllRegistrations()
{
    auth.isLoading('registrations');
    allRegistrations().then((dt) => {
        auth.hasLoaded('registrations');
        (dt.fencers || []).map((f:Fencer) => {
            let key = 'f' + f.id;
            if (!fencerList.value[key]) {
                fencerList.value[key] = { fencer: f, checkin: [], checkout: []};
            }
            f.registrations = [];
            fencerList.value[key].fencer = f;
        });

        (dt.registrations || []).map((r:Registration) => {
            let key = 's' + r.sideEventId;
            if (!registrationList.value[key]) {
                registrationList.value[key] = [];
            }
            if (r.fencerId && (!r.roleId || r.roleId == 0)) {
                registrationList.value[key].push(r.fencerId || 0);
                let fkey = 'f' + r.fencerId;
                if (fencerList.value[fkey] && fencerList.value[fkey].fencer && fencerList.value[fkey].fencer.registrations) {
                    fencerList.value[fkey].fencer.registrations.push(r);
                }
            }
        });

        (dt.documents || []).map((d:AccreditationDocument) => {
            let key = 'f' + d.fencerId;
            if (fencerList.value[key]) {
                if (d.checkin && !d.checkout) {
                    var lst = fencerList.value[key].checkin.filter((v) => v != d.checkin);
                    lst.push(d.checkin);
                    fencerList.value[key].checkin = lst;
                }
                else if (d.checkout) {
                    var lst = fencerList.value[key].checkout.filter((v) => v != d.checkout);
                    lst.push(d.checkout);
                    fencerList.value[key].checkout = lst;
                }
            }
        });
    })
    .catch((e) => {
        auth.hasLoaded('registrations');
        console.log(e);
        alert('There was an error retrieving the registration data. Please reload the page and try again');
    });    
}

function onSearch(event:SideEvent)
{
    if (registrationList.value['s' + event.id]) {
        selectedEvent.value = event;
    }
}

function onDialogClose()
{
    selectedEvent.value = null;
}

import CompetitionLine from './CompetitionLine.vue';
import DTDialog from './DTDialog.vue';
</script>
<template>
    <div class="dt-interface" v-if="auth.isDT(auth.eventId, 'code')">
        <div class="competition-line">
            <div class="title"></div>
            <div class="date"></div>
            <div class="number"><b>Reg</b></div>
            <div class="number"><b>Pending</b></div>
            <div class="number"><b>Present</b></div>
            <div class="number"><b>Cancel</b></div>
            <div class="number"><b>Checkin</b></div>
            <div class="number"><b>CheckOut</b></div>
        </div>
        <CompetitionLine v-for="event in compListByDate" :key="event.id" 
            :event="event"
            :statLine="statList['s' + event.id]"
            @on-search="(e) => onSearch(e)"
        />
        <DTDialog
            :event="selectedEvent"
            :registrations="registrationList['s' + selectedEvent?.id]"
            :fencers="fencerList"
            :visible="selectedEvent != null"
            @on-close="onDialogClose"
        />
    </div>
</template>