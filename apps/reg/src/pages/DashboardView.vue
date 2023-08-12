<script lang="ts" setup>
import { useAuthStore } from '../../../common/stores/auth';
const authStore = useAuthStore();

function waitAsGuest()
{
    return false;// && !authStore || (authStore.isGuest && authStore.userId == -1);
}

function getVersion()
{
    return import.meta.env.VITE_VERSION;
}

function eventTitle()
{
    return "Just an event";
}

import { ElIcon, ElContainer, ElHeader, ElFooter, ElMain } from 'element-plus';
import HeaderBar from '../components/HeaderBar.vue';
import TabInterface from '../components/TabInterface.vue';
import FooterBar from '../components/FooterBar.vue';
import { Loading } from '@element-plus/icons-vue';
</script>
<template>
    <ElContainer>
        <ElHeader>
            <HeaderBar />
        </ElHeader>
        <ElMain>
            <div v-if="waitAsGuest()">
                <h3>Login</h3>
                <p>Please wait while loading</p>
                <ElIcon class="is-loading">
                    <Loading />
                </ElIcon>
            </div>
            <div v-else class="full">
                <div class="main-header">
                    <div class="event-title">Registration for: {{ eventTitle() }}</div>
                    <div class="version">{{ getVersion() }}</div>
                </div>
                <TabInterface />
            </div>
        </ElMain>
        <ElFooter>
            <FooterBar />
        </ElFooter>
    </ElContainer>
</template>
