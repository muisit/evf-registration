<script lang="ts" setup>
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
const auth = useAuthStore();
const basic = useBasicStore();

function showCheckinInterface()
{
    return auth.isCheckin(auth.eventId, 'code') && !basic.eventCombinesCheckinCheckout();
}

function showCheckoutInterface()
{
    return auth.isCheckout(auth.eventId, 'code') || (auth.isCheckin(auth.eventId, 'code') && basic.eventCombinesCheckinCheckout());
}

import InputComponent from '../components/InputComponent.vue';
import ScanLogger from './ScanLogger.vue';
import AccreditationInterface from './AccreditationInterface.vue';
import AdminInterface from './AdminInterface.vue';
import CheckinInterface from './CheckinInterface.vue';
import CheckoutInterface from './CheckoutInterface.vue';
import DTInterface from './DTInterface.vue';
</script>
<template>
    <div :class="{
        accredit: true,
        admin: auth.isOrganiser(auth.eventId, 'code'),
        accreditation: auth.isAccreditor(auth.eventId, 'code'),
        checkin: auth.isCheckin(auth.eventId, 'code'),
        checkout: auth.isCheckout(auth.eventId, 'code'),
        dt: auth.isDT(auth.eventId, 'code')
    }" v-if="!auth.isGuest">
        <InputComponent />
        <ScanLogger/>
        <AdminInterface :visible="auth.isOrganiser(auth.eventId, 'code')"/>
        <AccreditationInterface :visible="auth.isAccreditor(auth.eventId, 'code')"/>
        <CheckinInterface :visible="showCheckinInterface()"/>
        <CheckoutInterface :visible="showCheckoutInterface()"/>
        <DTInterface :visible="auth.isDT(auth.eventId, 'code')"/>
    </div>
</template>