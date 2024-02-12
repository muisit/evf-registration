<script lang="ts" setup>
import { useDataStore } from '../stores/data';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
import { is_valid } from '../../../common/functions';

const data = useDataStore();
const auth = useAuthStore();
const basic = useBasicStore();

function getVersion()
{
    return import.meta.env.VITE_VERSION;
}

function doLogout()
{
    auth.logOut().then(() => {
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
                <div class="event-title" v-if="is_valid(basic.event)">
                    {{ basic.event.name }}
                    <div class="subtitle">{{ data.subtitle }}</div>
                </div>
                <div class="event-title" v-if="!is_valid(basic.event)">No event selected</div>
                <div v-if="auth.codeUser" class="username">{{ auth.userName }}</div>
                <div v-if="auth.codeUser" class="logout"><ElButton @click="doLogout" type="primary">Logout</ElButton></div>
                <div class="version">{{ getVersion() }}</div>
            </div>

            <div v-if="!is_valid(basic.event)" class="start-screen">
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
