<script lang="ts" setup>
import { watch } from 'vue';
import { useDataStore } from '../stores/data';
import { useAuthStore } from '../../../common/stores/auth';
import { is_valid } from '../../../common/functions';
const props = defineProps<{
    event:number;
}>();

const data = useDataStore();
const auth = useAuthStore();

watch(
    () => props.event,
    (nw) => {
        data.getEvent(nw);
    },
    { immediate: true }
);

function getVersion()
{
    return import.meta.env.VITE_VERSION;
}

function doLogout()
{
    auth.logOut().then(() => {
        auth.eventId = 0;
        data.logout();
    });
}

import { ElContainer, ElHeader, ElFooter, ElMain, ElButton } from 'element-plus';
import HeaderBar from '../../../common/components/HeaderBar.vue'
import FooterBar from '../../../common/components/FooterBar.vue';
import MainInterface from '../components/MainInterface.vue';
import LoginInterface from '../components/LoginInterface.vue';
</script>
<template>
    <ElContainer>
        <ElHeader>
            <HeaderBar />
        </ElHeader>
        <ElMain>
            <div class="main-header">
                <div class="event-title" v-if="is_valid(data.event)">{{ data.event.name }}</div>
                <div class="event-title" v-if="!is_valid(data.event)">No event selected</div>
                <div v-if="auth.codeUser" class="username">{{ auth.userName }}</div>
                <div v-if="auth.codeUser" class="logout"><ElButton @click="doLogout" type="primary">Logout</ElButton></div>
                <div class="version">{{ getVersion() }}</div>
            </div>

            <div v-if="!is_valid(data.event)" class="start-screen">
                <LoginInterface />
            </div>
            <div v-else class="full">
                <MainInterface />
            </div>
        </ElMain>
        <ElFooter>
            <FooterBar />
        </ElFooter>
    </ElContainer>
</template>
