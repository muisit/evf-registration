<script lang="ts" setup>
import { ref, computed } from 'vue';
import type  { Ref } from 'vue';
import type { SideEvent } from "../../../common/api/schemas/sideevent";
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { FencerDataById } from './lib/types';
import type { Registration } from '../../../common/api/schemas/registration';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';

const props = defineProps<{
    visible:boolean;
    event:SideEvent|null;
    registrations?: number[];
    fencers: FencerDataById;
}>();
const emits = defineEmits(['onClose']);
const auth = useAuthStore();
const basic = useBasicStore();

function closeForm()
{
    emits('onClose');
}

interface FencerSummary {
    id: number;
    lastname: string;
    firstname: string;
    country: string;
    registered: boolean;
    present: boolean;
    absent: boolean;
    checkin: number;
    checkout: number;
}

function getCountry(fencer:Fencer)
{
    let cid = fencer.countryId;
    if (fencer.registrations) {
        fencer.registrations.map((r:Registration) => {
            if (r.sideEventId == props.event?.id && r.countryId) cid = r.countryId;
        });
    }
    return basic.countriesById['c' + cid];
}

const fencerlist:Ref<FencerSummary[]> = computed(() => {
    return ((props.registrations || []).map((fencerId:number) => {
        let key = 'f' + fencerId;
        if (props.fencers[key]) {
            let fencer = props.fencers[key].fencer;
            let summary:FencerSummary = {
                id: fencer.id,
                lastname: fencer.lastName || '',
                firstname: fencer.firstName || '',
                country: getCountry(fencer)?.abbr,
                registered: (fencer.registrations || []).filter((r:Registration) => r.sideEventId == props.event?.id).length > 0,
                present: (fencer.registrations || []).filter((r:Registration) => r.sideEventId == props.event?.id && r.state == 'P').length > 0,
                absent: (fencer.registrations || []).filter((r:Registration) => r.sideEventId == props.event?.id && r.state == 'A').length > 0,
                checkin: props.fencers[key].checkin.length,
                checkout: props.fencers[key].checkout.length
            };
            return summary;
        }
        return null;
    }).filter((v) => v != null)) as FencerSummary[];
});

const sortValue:Ref<string[]> = ref(['c']);

const sortedList:Ref<FencerSummary[]> = computed(() => {
    let retval = fencerlist.value.slice();
    retval = retval.sort((f1:FencerSummary, f2:FencerSummary) => {
        for (let i in sortValue.value) {
            let s = sortValue.value[i];
            if (s == 'c' || s == 'C') {
                if (f1.country != f2.country) {
                    return f1.country < f2.country ? (s == 'c' ? -1 : 1) : (s == 'c' ? 1 : -1);
                }
            }
            if (s == 'l' || s == 'L') {
                if (f1.lastname != f2.lastname) {
                    return f1.lastname < f2.lastname ? (s == 'l' ? -1 : 1) : (s == 'l' ? 1 : -1);
                }
            }
            if (s == 'f' || s == 'F') {
                if (f1.firstname != f2.firstname) {
                    return f1.firstname < f2.firstname ? (s == 'f' ? -1 : 1) : (s == 'f' ? 1 : -1);
                }
            }
            if (s == 's' || s == 'S') {
                if (f1.present) {
                    if (!f2.present) {
                        return s == 's' ? -1 : 1;
                    }
                }
                else if (f1.absent) {
                    if (f2.present || !f2.absent) {
                        return s == 's' ? 1 : -1;
                    }
                }
                else if (f2.present || f2.absent) {
                    return f2.present ? (s == 's' ? 1 : -1)  : (s == 's' ? -1 : 1);
                }
            }
            if (s == 'i' || s == 'I') {
                if (f1.checkin != f2.checkin) {
                    return f1.checkin > f2.checkin ? (s == 'i' ? -1 : 1) : (s == 'i' ? 1 : -1);
                }
            }
            if (s == 'o' || s == 'O') {
                if (f1.checkout != f2.checkout) {
                    return f1.checkout > f2.checkout ? (s == 'o' ? -1 : 1) : (s == 'o' ? 1 : -1);
                }
            }
        }
        return f1.id < f2.id ? -1 : 1;
    });
    return retval;
});

const totalRegistered = computed(() => {
    return fencerlist.value.length;
});
const totalPresent = computed(() => {
    return fencerlist.value.filter((s:FencerSummary) => {
        return s.present == true;
    }).length;

});
const totalAbsent = computed(() => {
    return fencerlist.value.filter((s:FencerSummary) => {
        return s.absent == true;
    }).length;

});
const totalCheckin = computed(() => {
    return fencerlist.value.filter((s:FencerSummary) => {
        return s.checkin > 0;
    }).length;

});
const totalDone = computed(() => {
    return fencerlist.value.filter((s:FencerSummary) => {
        return s.present && s.checkout > 0;
    }).length;
});

function adjustSort(val:string[])
{
    sortValue.value = val;
}

import SortingIcon from './special/SortingIcon.vue';
import { ElDialog, ElButton } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" :title="'DT Overview for ' + props.event?.title" @close="() => closeForm()">
        <div class="summary">
            <span class="label first">Total: </span> {{ totalRegistered }}
            <span class="label">Present: </span> {{ totalPresent }}
            <span class="label">Absent: </span> {{ totalAbsent }}
            <span class="label">Checkin: </span> {{ totalCheckin }}
            <span class="label">Done: </span> {{ totalDone }}
        </div>
        <table class="dt-listing">
            <thead>
                <tr>
                    <th>Surname <SortingIcon name='l' :sorter="sortValue" @on-sort="adjustSort"/></th>
                    <th>Given name <SortingIcon name='f' :sorter="sortValue" @on-sort="adjustSort"/></th>
                    <th>Country <SortingIcon name='c' :sorter="sortValue" @on-sort="adjustSort"/></th>
                    <th>Present <SortingIcon name='s' :sorter="sortValue" @on-sort="adjustSort"/></th>
                    <th>Absent <SortingIcon name='s' :sorter="sortValue" @on-sort="adjustSort"/></th>
                    <th>Checkin <SortingIcon name='i' :sorter="sortValue" @on-sort="adjustSort"/></th>
                    <th>Checkout <SortingIcon name='o' :sorter="sortValue" @on-sort="adjustSort"/></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="fencer in sortedList" :key="fencer.id" :class="{
                    'registered': fencer.registered,
                    'absent': fencer.absent,
                    'present': fencer.present,
                    'checkin': fencer.checkin > 0 && fencer.present,
                    'done': fencer.checkout > 0 && fencer.present
                }">
                    <td>{{ fencer.lastname }}</td>
                    <td>{{ fencer.firstname }}</td>
                    <td>{{ fencer.country }}</td>
                    <td>{{ fencer.present ? 'X' : '' }}</td>
                    <td>{{ fencer.absent ? 'X' : '' }}</td>
                    <td>{{ fencer.checkin > 0 ? fencer.checkin : '' }}</td>
                    <td>{{ fencer.checkout > 0 ? fencer.checkout : '' }}</td>
                </tr>
            </tbody>
        </table>
        <template #footer>
            <span class="dialog-footer">
                <ElButton type="primary" @click="closeForm">Ok</ElButton>
            </span>
      </template>
    </ElDialog>
</template>