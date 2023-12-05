<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import type { Fencer, FencerList } from '../../../common/api/schemas/fencer';
import { defaultFencer } from '../../../common/api/schemas/fencer';
import { fencerlist } from '../../../common/api/fencers/fencerlist';
import { is_valid } from '../../../common/functions';
import { decorateFencer } from '../stores/lib/decorateFencer';
import { isOpenForRegistration } from '../../../common/lib/event';

const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();

watch(
    () => [props.visible, data.currentEvent, data.currentCountry],
    () => {
        if (props.visible) {
            data.getRegistrations();
        }
    },
    { immediate: true }
)

const searchDialog = ref(false);
const isSearching = ref(false);
const selectedFencer:Ref<Fencer> = ref(defaultFencer());
const fencerDialog = ref(false);
const selectionDialog = ref(false);

function openSearchDialog()
{
    searchDialog.value = true;
    selectedFencer.value = defaultFencer();
    isSearching.value = true;
}

function closeSearchDialog()
{
    searchDialog.value = false;
    // if we opened the fencerDialog, assume we are still searching
    isSearching.value = fencerDialog.value;
}

function saveSearchDialog(el:Fencer) 
{
    selectedFencer.value = el;
    fencerDialog.value = true;
}

function closeFencerDialog()
{
    fencerDialog.value = false;
    // if we opened the selection dialog, we are still searching
    isSearching.value = selectionDialog.value;
}

function saveFencerDialog()
{
    data.addFencer(selectedFencer.value);
    if (isSearching.value) {
        selectionDialog.value = true;
    }
}

function closeSelectionDialog()
{
    selectionDialog.value = false;
    isSearching.value = false;
}

function updateFencerDialog(fieldDef:any)
{
    switch (fieldDef.field) {
        case 'id':
            selectedFencer.value.id = fieldDef.value;
            break;
        case 'lastName':
            selectedFencer.value.lastName = fieldDef.value;
            break;
        case 'firstName':
            selectedFencer.value.firstName = fieldDef.value;
            break;
        case 'gender':
            selectedFencer.value.gender = fieldDef.value;
            break;
        case 'countryId':
            selectedFencer.value.countryId = fieldDef.value;
            break;
        case 'dateOfBirth':
            selectedFencer.value.dateOfBirth = fieldDef.value;
            break;
        case 'photoStatus':
            selectedFencer.value.photoStatus = fieldDef.value;
            break;
    }
}

function editFencer(fencer:Fencer)
{
    fencerDialog.value = true;
    selectedFencer.value = fencer;
}

function selectFencer(fencer:Fencer)
{
    selectionDialog.value = true;
    selectedFencer.value = fencer;
}

const allfencers:Ref<FencerList> = ref([]);
watch(
    () => [props.visible, data.currentCountry],
    (nw) => {
        if (is_valid(data.currentCountry.id) && props.visible) {
            fencerlist(data.currentCountry)
                .then((data:Fencer[]) => {
                    allfencers.value = data.map((f:Fencer) => decorateFencer(f));
                });
        }
        else {
            allfencers.value = [];
        }
    },
    { immediate: true }
)

import RegistrationHeader from '../components/registration/RegistrationHeader.vue';
import ParticipantList from '../components/registration/participantList/ParticipantList.vue';
import FencerDialog from '../components/registration/FencerDialog.vue';
import SearchDialog from '../components/registration/SearchDialog.vue';
import SelectionDialog from '../components/registration/SelectionDialog.vue';
import { ElButton } from 'element-plus';
</script>
<template>
    <div class="registration-page" v-if="props.visible">
        <RegistrationHeader :country-switch="auth.canSwitchCountry() || false"/>
        <div class='registration-buttons' v-if="isOpenForRegistration(data.currentEvent)">
            <ElButton type="primary" @click="openSearchDialog">Add Registration</ElButton>
        </div>
        <ParticipantList @on-edit="editFencer" @on-select="selectFencer"/>
        <SearchDialog @onClose="closeSearchDialog" @onSave="saveSearchDialog" :visible="searchDialog" :fencers="allfencers" />
        <FencerDialog @onClose="closeFencerDialog" @onUpdate="updateFencerDialog" @onSave="saveFencerDialog" :visible="fencerDialog" :fencer="selectedFencer" :changeCountry="auth.canSwitchCountry() || false"/>
        <SelectionDialog @onClose="closeSelectionDialog" :visible="selectionDialog" :fencer="selectedFencer" :isadmin="auth.canRegister()"/>
    </div>
</template>