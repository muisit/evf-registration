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

function canAssignOrgRoles()
{
    // sysop, organiser and registrars, not HoD or superHoD
    return auth.isSysop() || auth.isOrganiser(data.currentEvent.id) || auth.isRegistrar(data.currentEvent.id);
}

function onChangeCountry(newValue)
{
    console.log('on change of country in registration page', newValue);
    data.setCountry(newValue.countryId);
}

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            console.log('getting registrations on visible');
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

function closeFencerDialog() {
    console.log('closing fencer dialog');
    fencerDialog.value = false;
}

function saveFencerDialog(form:object) {
    console.log('saving fencers from dialog');
}

function changeFencerDialog(field:any)
{
    //selectedFencer.value[field.fieldName] = field.value;
}

const allfencers:Ref<FencerList> = ref([]);
watch(
    () => data.currentCountry,
    (nw) => {
        console.log('retrieving new list of fencers for this country', nw);
        if (is_valid(nw)) {
            fencerlist(nw)
                .then((data) => {
                    allfencers.value = data;
                });
        }
        else {
            console.log('not a valid country');
            allfencers.value = [];
        }
    },
    { immediate: true }
)

import RegistrationHeader from '../components/registration/RegistrationHeader.vue';
import ParticipantList from '../components/registration/ParticipantList.vue';
import FencerDialog from '../components/registration/FencerDialog.vue';
import SearchDialog from '../components/registration/SearchDialog.vue';
import { ElButton } from 'element-plus';
</script>
<template>
    <div class="registration-page">
        <RegistrationHeader :country-switch="canSwitchCountry()" @onChangeCountry="onChangeCountry"/>
        <div class='registration-buttons'>
            <ElButton type="primary" @click="openSearchDialog">Add Registration</ElButton>
        </div>
        <ParticipantList />
        <SearchDialog @onClose="closeSearchDialog" @onSave="saveSearchDialog" :visible="searchDialog" :fencers="allfencers" />
        <FencerDialog @onClose="closeFencerDialog" @onSave="saveFencerDialog" @onChange="changeFencerDialog" :visible="fencerDialog" :fencer="selectedFencer" />
    </div>
</template>