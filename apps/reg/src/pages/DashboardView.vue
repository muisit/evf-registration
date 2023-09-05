<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';

const authStore = useAuthStore();
const dataStore = useDataStore();
const loginVisible = ref(waitAsGuest());

function waitAsGuest()
{
    return !authStore || authStore.isGuest;
}

function getVersion()
{
    return import.meta.env.VITE_VERSION;
}

function eventTitle()
{
    return "Just an event";
}

function onCloseLogin()
{
    loginVisible.value = waitAsGuest();
}

function onLogin(credentials:object)
{
    authStore.logIn(credentials.username, credentials.password)
        .then((data) => {
            if (data.status == 'error') {
                alert('There was an error with the supplied credentials. Please try again.');
                loginVisible.value = true;
            }
        })
        .catch((e) => {
            alert('There was a network error. Please try again.' + e);
            loginVisible.value = true;
        });
}

dataStore.getBasicData();

import { ElIcon, ElContainer, ElHeader, ElFooter, ElMain } from 'element-plus';
import HeaderBar from '../components/HeaderBar.vue';
import TabInterface from '../components/TabInterface.vue';
import FooterBar from '../components/FooterBar.vue';
import LoginDialog from '../components/LoginDialog.vue';
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
                <LoginDialog @on-close="onCloseLogin" @on-save="onLogin" v-if="loginVisible"/>
                <div v-if="!loginVisible">
                    <p>Please wait while loading</p>
                    <ElIcon class="is-loading">
                        <Loading />
                    </ElIcon>
                </div>
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
