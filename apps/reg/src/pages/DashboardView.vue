<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { is_valid } from '../../../common/functions';

const authStore = useAuthStore();
const dataStore = useDataStore();
const loginVisible = ref(waitAsGuest());

function waitAsGuest()
{
    return !authStore || authStore.isGuest;
}

function noEventsAvailable()
{
    return authStore && !authStore.isGuest && dataStore && dataStore.events.length < 1;
}

function getVersion()
{
    return import.meta.env.VITE_VERSION;
}

function onCloseLogin()
{
    loginVisible.value = waitAsGuest();
}

function onLogin(credentials:any)
{
    authStore.logIn(credentials.username, credentials.password)
        .then((data) => {
            if (data.status == 'error') {
                alert('There was an error with the supplied credentials. Please try again.');
                loginVisible.value = true;
            }
            else {
                if (authStore.isHod() && authStore.countryId && dataStore.countries.length) {
                    dataStore.setCountry(authStore.countryId);
                }
                // retrieve the list of events
                dataStore.getEvents();
            }
        })
        .catch((e) => {
            alert('There was a network error. Please try again.' + e);
            loginVisible.value = true;
        });
}

import { ElIcon, ElContainer, ElHeader, ElFooter, ElMain } from 'element-plus';
import HeaderBar from '../components/HeaderBar.vue';
import TabInterface from '../components/TabInterface.vue';
import FooterBar from '../components/FooterBar.vue';
import LoginDialog from '../components/LoginDialog.vue';
import EventSelection from '../components/special/EventSelection.vue';
import { Loading } from '@element-plus/icons-vue';
</script>
<template>
    <ElContainer>
        <ElHeader>
            <HeaderBar />
        </ElHeader>
        <ElMain>
            <div v-if="waitAsGuest()">
                <h3 class="textcenter">Login</h3>
                <LoginDialog @on-close="onCloseLogin" @on-save="onLogin" v-if="loginVisible"/>
                <div v-if="!loginVisible">
                    <p>Please wait while loading</p>
                    <ElIcon class="is-loading">
                        <Loading />
                    </ElIcon>
                </div>
            </div>
            <div v-else-if="noEventsAvailable() && authStore.isLoading">
                <p>Please wait while loading data</p>
            </div>
            <div v-else-if="noEventsAvailable()">
                <h3 class="textcenter">No events found</h3>
                <p>It seems no events are currently available to manage. Please contact the
                    <a href="mailto:webmaster@veteransfencing.eu">webmaster</a> if this seems
                    to be an error.
                </p>
            </div>
            <div v-else class="full">
                <div class="main-header">
                    <div class="event-title" v-if="is_valid(dataStore.currentEvent.id)">Registration for: {{ dataStore.currentEvent.name }}</div>
                    <div class="event-title" v-else>Please wait while loading</div>
                    <div class="event-selection">
                        <EventSelection />
                    </div>
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
