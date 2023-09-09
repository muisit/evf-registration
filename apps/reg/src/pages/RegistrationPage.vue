<script lang="ts" setup>
import { watch } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
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


import RegistrationHeader from '../components/registration/RegistrationHeader.vue';
import ParticipantList from '../components/registration/ParticipantList.vue';
</script>
<template>
    <div class="registration-page">
        <RegistrationHeader :country-switch="canSwitchCountry()" @onChangeCountry="onChangeCountry"/>
        <ParticipantList />
    </div>
</template>