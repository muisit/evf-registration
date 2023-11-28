<script lang="ts" setup>
import { Ref, ref, watch } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { Fencer, FencerList, defaultFencer } from '../../../common/api/schemas/fencer';
import { fencerlist } from '../../../common/api/fencers/fencerlist';
import { is_valid } from '../../../common/functions';
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

function onChangeCountry(newValue)
{
    data.setCountry(newValue.countryId);
}

watch(
    () => [props.visible, data.currentEvent, data.currentCountry],
    () => {
        if (props.visible) {
            auth.isLoading = true;
            data.getRegistrations().then(() => { auth.isLoading = false });
        }
    },
    { immediate: true }
)

import CashierHeader from '../components/cashier/CashierHeader.vue';
import ParticipantList from '../components/cashier/ParticipantList.vue';
import CashierFooter from '../components/cashier/CashierFooter.vue';
import { ElButton } from 'element-plus';
</script>
<template>
    <div class="cashier-page" v-if="props.visible">
        <CashierHeader :country-switch="canSwitchCountry()" @onChangeCountry="onChangeCountry"/>
        <ParticipantList />
        <CashierFooter/>
    </div>
</template>
