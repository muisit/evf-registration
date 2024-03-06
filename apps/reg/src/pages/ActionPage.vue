<script lang="ts" setup>
import { ref, watch, computed } from 'vue';
import { statistics } from '../../../common/api/event/statistics';
import { allRegistrations } from '../../../common/api/registrations/registrations';
import { decorateFencer } from '../stores/lib/decorateFencer';
import type { EventStatistics } from '../../../common/api/schemas/eventstatistics';
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { Registrations } from '../../../common/api/schemas/registrations';
import type { Ref } from 'vue';
import { useDataStore } from '../stores/data';
const props = defineProps<{
    visible:boolean;
}>();

const data = useDataStore();
const statisticsValue:Ref<EventStatistics> = ref({
    id: 0,
    registrations: 0,
    participants: 0,
    organisers: 0,
    support: 0,
    hasPicture: 0,
    hasNewPicture: 0,
    hasNoPicture: 0,
    hasReplacePicture: 0,
    queue: 0,
    failed: 0
});
const selectedFencers:Ref<Registrations> = ref({registrations:[], fencers: [], documents: []});
const selectedFencer:Ref<Fencer|null> = ref(null);
const currentIndex:Ref<number> = ref(0);
const currentSelection:Ref<string> = ref('new');
const showDialog = ref(false);

watch(() => [props.visible, data.currentEvent],
    (nw) => {
        if (nw[0]) {
            statistics().then((dt:EventStatistics) => {
                if (dt.id == data.currentEvent?.id) {
                    statisticsValue.value = dt
                }
            });
            allRegistrations().then((dt:Registrations) => {
                selectedFencers.value.registrations = dt.registrations;
                if (dt.fencers !== null) {
                    selectedFencers.value.fencers = dt.fencers.map((f:Fencer) => {
                        return decorateFencer(f);
                    });
                }
            });
        }
    }
);

function openDialog(selection:string)
{
    selectedFencer.value = null;
    currentSelection.value = selection;
    currentIndex.value = -1;
    nextFencer();
    if (selectedFencer.value !== null) {
        showDialog.value = true;
    }
}

function nextFencer()
{
    currentIndex.value += 1;
    if (selectedFencers.value.fencers) {
        for (; currentIndex.value < selectedFencers.value.fencers.length; currentIndex.value++) {
            let fencer = selectedFencers.value.fencers[currentIndex.value];
            if (isSelectableFencer(fencer, currentSelection.value)) {
                selectedFencer.value = fencer;
                break;
            }
        }
    }
}

function hasNextFencer()
{
    let index = currentIndex.value + 1;
    if (selectedFencers.value.fencers) {
        for (; index < selectedFencers.value.fencers.length; index++) {
            let fencer = selectedFencers.value.fencers[index];
            if (isSelectableFencer(fencer, currentSelection.value)) {
                return true;
            }
        }
    }
    return false;
}

function previousFencer()
{
    currentIndex.value -= 1;
    if (selectedFencers.value.fencers) {
        for (; currentIndex.value >= 0; currentIndex.value--) {
            let fencer = selectedFencers.value.fencers[currentIndex.value];
            if (isSelectableFencer(fencer, currentSelection.value)) {
                selectedFencer.value = fencer;
                break;
            }
        }
    }
}

function hasPreviousFencer()
{
    let index = currentIndex.value - 1;
    if (selectedFencers.value.fencers) {
        for (; index >= 0; index--) {
            let fencer = selectedFencers.value.fencers[index];
            if (isSelectableFencer(fencer, currentSelection.value)) {
                return true;
            }
        }
    }
    return false;
}

function isSelectableFencer(fencer:Fencer, selection:string)
{
    if (selection == 'new' && fencer.photoStatus == 'Y') return true;
    if (selection == 'replace' && fencer.photoStatus == 'R') return true;
    return false;
}

function closeFencerDialog()
{
    showDialog.value = false;
}

function updateFencerDialog(fieldDef:any)
{
    switch (fieldDef.field) {
        case 'photoStatus':
            if (selectedFencer.value !== null) {
                selectedFencer.value.photoStatus = fieldDef.value;
            }
            break;
    }
}

function saveFencerDialog()
{
    // do we need to replace the fencer in the selectedFencers list... 
}

function gotoFencer(dir:string)
{
    if (dir == 'previous') {
        if (hasPreviousFencer()) {
            previousFencer();
        }
        else {
            showDialog.value = false;
        }
    }
    else {
        if (hasNextFencer()) {
            nextFencer();
        }
        else {
            showDialog.value = false;
        }
    }
}

const hasNewPicture = computed(() => {
    let total = 0;
    if (selectedFencers.value.fencers) {
        for (let index = 0; index < selectedFencers.value.fencers.length; index++) {
            let fencer = selectedFencers.value.fencers[index];
            if (fencer.photoStatus == 'Y') {
                total += 1;
            }
        }
    }
    return total;
});

const hasReplacePicture = computed(() => {
    let total = 0;
    if (selectedFencers.value.fencers) {
        for (let index = 0; index < selectedFencers.value.fencers.length; index++) {
            let fencer = selectedFencers.value.fencers[index];
            if (fencer.photoStatus == 'R') {
                total += 1;
            }
        }
    }
    return total;
});

import { ElButton } from 'element-plus';
import FencerPictureDialog from '../components/actions/FencerPictureDialog.vue';
</script>
<template>
    <div class="actions-page" v-if="props.visible">
        <div class="actions-header">
            <h2>General Actions</h2>
        </div>
        <table class="statistics">
            <tr>
                <td class="label">Registrations</td>
                <td>{{  statisticsValue.registrations }}</td>
            </tr>
            <tr>
                <td class="label">Participants</td>
                <td>{{  statisticsValue.participants }}</td>
            </tr>
            <tr>
                <td class="label">Support</td>
                <td>{{  statisticsValue.support }}</td>
            </tr>
            <tr>
                <td class="label">Organisers</td>
                <td>{{  statisticsValue.organisers }}</td>
            </tr>
            <tr><td colspan='2'><hr/></td></tr>
            <tr>
                <td class="label">Picture: OK</td>
                <td>{{  statisticsValue.hasPicture }}</td>
            </tr>
            <tr>
                <td class="label">Picture: New</td>
                <td>{{  hasNewPicture }}</td>
                <td v-if="hasNewPicture > 0">
                    <ElButton @click="() => openDialog('new')">Check</ElButton>
                </td>
            </tr>
            <tr>
                <td class="label">Picture: Replace</td>
                <td>{{ hasReplacePicture }}</td>
                <td v-if="hasReplacePicture > 0">
                    <ElButton @click="() => openDialog('replace')">Check</ElButton>
                </td>
            </tr>
            <tr>
                <td class="label">Picture: NONE</td>
                <td>{{  statisticsValue.hasNoPicture }}</td>
            </tr>
            <tr><td colspan='2'><hr/></td></tr>
            <tr>
                <td class="label">Queue</td>
                <td>{{  statisticsValue.queue }}</td>
            </tr>
            <tr>
                <td class="label">Failed jobs</td>
                <td>{{  statisticsValue.failed }}</td>
            </tr>
        </table>
        <div class="actions-footer">

        </div>
        <FencerPictureDialog v-if="selectedFencer !== null" @goto="gotoFencer" @onClose="closeFencerDialog" @onUpdate="updateFencerDialog" @onSave="saveFencerDialog" :fencer="selectedFencer" :visible="showDialog" :hasPrevious="hasPreviousFencer()" :hasNext="hasNextFencer()" />
    </div>
</template>
