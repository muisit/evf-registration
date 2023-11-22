<script lang="ts" setup>
import { Ref, ref, watch } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { Fencer, FencerList, defaultFencer } from '../../../common/api/schemas/fencer';
import { fencerlist } from '../../../common/api/fencers/fencerlist';
import { is_valid } from '../../../common/functions';
import { addFencerToRegistrationData } from '../components/registration/lib/addFencerToRegistrationData';
const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();

function canSwitchCountry()
{
    // system administrators, super-HoDs (general administrators), the event organiser and the event registrar can change countries
    return auth.isSysop() || auth.isOrganiser(data.currentEvent.id) || auth.isSuperHod() || auth.isRegistrar(data.currentEvent.id);
}

function isAdmin()
{
    // administrators are sysop and event organisers
    return auth.isSysop() || auth.isOrganiser(data.currentEvent.id) || auth.isRegistrar(data.currentEvent.id);
}

function onChangeCountry(newValue)
{
    data.setCountry(newValue.countryId);
}

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            data.getRegistrations();
        }
    },
    { immediate: true }
)

const searchDialog = ref(false);

function openSearchDialog()
{
    searchDialog.value = true;
}
function closeSearchDialog()
{
    searchDialog.value = false;
}
function saveSearchDialog(el:Fencer) 
{
    console.log('setting selectedFencer to ', el);
    selectedFencer.value = el;
    fencerDialog.value = true;
}

const fencerDialog = ref(false);
const selectedFencer:Ref<Fencer> = ref(defaultFencer());

function closeFencerDialog()
{
    console.log('closing fencer dialog');
    fencerDialog.value = false;
}

function saveFencerDialog()
{
    addFencerToRegistrationData(selectedFencer.value);
}

function updateFencerDialog(fieldDef:any)
{
    switch (fieldDef.field) {
        case 'id':
        case 'lastName':
        case 'firstName':
        case 'gender':
        case 'countryId':
        case 'dateOfBirth':
        case 'photoStatus':
            console.log('setting field ', fieldDef.field, fieldDef.value);
            selectedFencer.value[fieldDef.field] = fieldDef.value;
            break;
    }
}

function editFencer(fencer:Fencer)
{
    fencerDialog.value = true;
    selectedFencer.value = fencer;
}

const selectionDialog = ref(false);
function closeSelectionDialog()
{
    selectionDialog.value = false;
}

function selectFencer(fencer:Fencer)
{
    selectionDialog.value = true;
    selectedFencer.value = fencer;
}

const allfencers:Ref<FencerList> = ref([]);
watch(
    () => data.currentCountry,
    (nw) => {
        if (is_valid(nw)) {
            fencerlist(nw)
                .then((data) => {
                    allfencers.value = data;
                });
        }
        else {
            allfencers.value = [];
        }
    },
    { immediate: true }
)

import RegistrationHeader from '../components/registration/RegistrationHeader.vue';
import ParticipantList from '../components/registration/ParticipantList.vue';
import FencerDialog from '../components/registration/FencerDialog.vue';
import SearchDialog from '../components/registration/SearchDialog.vue';
import SelectionDialog from '../components/registration/SelectionDialog.vue';
import { ElButton } from 'element-plus';
</script>
<template>
    <div class="registration-page">
        <RegistrationHeader :country-switch="canSwitchCountry()" @onChangeCountry="onChangeCountry"/>
        <div class='registration-buttons'>
            <ElButton type="primary" @click="openSearchDialog">Add Registration</ElButton>
        </div>
        <ParticipantList @on-edit="editFencer" @on-select="selectFencer"/>
        <SearchDialog @onClose="closeSearchDialog" @onSave="saveSearchDialog" :visible="searchDialog" :fencers="allfencers" />
        <FencerDialog @onClose="closeFencerDialog" @onUpdate="updateFencerDialog" @onSave="saveFencerDialog" :visible="fencerDialog" :fencer="selectedFencer" :changeCountry="canSwitchCountry()"/>
        <SelectionDialog @onClose="closeSelectionDialog" :visible="selectionDialog" :fencer="selectedFencer" :isadmin="isAdmin()"/>
    </div>
</template>