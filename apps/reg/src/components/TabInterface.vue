<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
const auth = useAuthStore();
const data = useDataStore();

const activeTab = ref('overview');

function onTabChange(name:TabPaneName)
{
    if (name == 'logout') {
        console.log("calling logout");
        auth.logOut().then(() => {
            activeTab.value = 'overview';
        });
    }
}

function canRegister()
{
    return canOrganise() || auth.isRegistrar(data.currentEvent.id) || auth.isHod();
}

function canCashier()
{
    return canOrganise() || auth.isCashier(data.currentEvent.id) || auth.isHod();
}

function canAccredit()
{
    return canOrganise() || auth.isAccreditor(data.currentEvent.id);
}

function canOrganise()
{
    return auth.isSysop() || auth.isOrganiser(data.currentEvent.id);
}

import { ElTabs, ElTabPane } from 'element-plus';
import type { TabPaneName } from 'element-plus';
import OverviewPage from '../pages/OverviewPage.vue';
import RegistrationPage from '../pages/RegistrationPage.vue';
import CashierPage from '../pages/CashierPage.vue';
</script>
<template>
    <ElTabs type="card" @tab-change="onTabChange" v-model="activeTab">
        <ElTabPane label="Overview" name="overview">
            <OverviewPage :visible="activeTab == 'overview'" @change-tab="(e) => activeTab = e"/>
        </ElTabPane>
        <ElTabPane v-if="canRegister()" label="Registration" name="registration">
            <RegistrationPage :visible="activeTab == 'registration'"/>
        </ElTabPane>
        <ElTabPane v-if="canCashier()" label="Cashier" name="cashier">
            <CashierPage :visible="activeTab == 'cashier'"/>
        </ElTabPane>
        <ElTabPane v-if="canAccredit()" label="Badges" name="badges">Pane 4</ElTabPane>
        <ElTabPane v-if="canOrganise()" label="Actions" name="actions">Pane 5</ElTabPane>
        <ElTabPane v-if="auth.isSysop()" label="Templates" name="templates">Pane 6</ElTabPane>
        <ElTabPane label="Logout" name="logout"></ElTabPane>
    </ElTabs>
</template>