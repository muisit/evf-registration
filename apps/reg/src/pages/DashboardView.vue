<script lang="ts" setup>
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
import { is_valid } from '../../../common/functions';
const props = defineProps<{
    event:string;
}>();

const authStore = useAuthStore();
const dataStore = useDataStore();

function waitAsGuest()
{
    return authStore && !authStore.registrationUser && !authStore.isCurrentlyLoading();
}

function nothingAvailable()
{
    return !authStore || (!authStore.registrationUser && authStore.isCurrentlyLoading());
}

function noEventsAvailable()
{
    return authStore && authStore.registrationUser && dataStore && dataStore.events.length < 1 && !authStore.isCurrentlyLoading();
}

function getVersion()
{
    return import.meta.env.VITE_VERSION;
}

function onCloseLogin()
{
}

function onLogin(credentials:any)
{
    authStore.logIn(credentials.username, credentials.password)
        .then((data) => {
            if (data.status == 'error') {
                alert('There was an error with the supplied credentials. Please try again.');
            }
            else {
                if (authStore.isHod() && authStore.countryId && dataStore.countries.length) {
                    dataStore.setCountry(authStore.countryId);
                }
            }
        })
        .catch((e) => {
            alert('There was a network error. Please try again.');
        });
}

import { ElIcon, ElContainer, ElHeader, ElFooter, ElMain } from 'element-plus';
import HeaderBar from '../../../common/components/HeaderBar.vue'
import TabInterface from '../components/TabInterface.vue';
import FooterBar from '../../../common/components/FooterBar.vue';
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
            <div class="main-header">
                <div class="event-title" v-if="is_valid(dataStore.currentEvent.id)">Registration for: {{ dataStore.currentEvent.name }}</div>
                <div class="event-selection">
                    <EventSelection />
                </div>
                <div class="version">{{ getVersion() }}</div>
            </div>

            <div v-if="nothingAvailable()" class="full">
            </div>
            <div v-else-if="noEventsAvailable()">
                <h3 class="textcenter">No events found</h3>
                <p>It seems no events are currently available to manage. Please contact the
                    <a href="mailto:webmaster@veteransfencing.eu">webmaster</a> if this seems
                    to be an error.
                </p>
            </div>
            <div v-else-if="waitAsGuest()">
                <h3 class="textcenter">Login</h3>
                <LoginDialog @on-close="onCloseLogin" @on-save="onLogin" v-if="waitAsGuest()"/>
                <div v-else>
                    <p>Please wait while loading</p>
                    <ElIcon class="is-loading">
                        <Loading />
                    </ElIcon>
                </div>
            </div>
            <div v-else class="full">
                <TabInterface />
            </div>
        </ElMain>
        <ElFooter>
            <FooterBar />
        </ElFooter>
    </ElContainer>
</template>
