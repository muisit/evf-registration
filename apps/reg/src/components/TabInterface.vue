<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useRouter } from 'vue-router';
const auth = useAuthStore();

const activeTab = ref('overview');

function onTabChange(name:string)
{
    console.log(name);
    if (name == 'logout') {
        console.log("calling logout");
        auth.logOut().then(() => {
            activeTab.value = 'overview';
        });
    }
}

import  { ElTabs, ElTabPane } from 'element-plus';
</script>
<template>
    <ElTabs type="card" @tab-change="onTabChange" v-model="activeTab">
        <ElTabPane label="Overview" name="overview">Pane 1</ElTabPane>
        <ElTabPane label="Registration" name="registration">Pane 2</ElTabPane>
        <ElTabPane label="Cashier" name="cashier">Pane 3</ElTabPane>
        <ElTabPane label="Badges" name="badges">Pane 4</ElTabPane>
        <ElTabPane label="Actions" name="actions">Pane 5</ElTabPane>
        <ElTabPane label="Templates" name="templates">Pane 6</ElTabPane>
        <ElTabPane label="Logout" name="logout"></ElTabPane>
    </ElTabs>
    
</template>