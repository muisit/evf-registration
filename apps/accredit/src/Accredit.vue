<script lang="ts" setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { useDataStore } from './stores/data';
import { useAuthStore } from '../../common/stores/auth';
import { useBasicStore } from '../../common/stores/basic';
import { is_valid } from '../../common/functions';
const props = defineProps<{
    event:number;
}>();

const data = useDataStore();
const basicStore = useBasicStore();

onMounted(() => {
    document.addEventListener('keydown', onTrackKey);
    document.addEventListener('paste', onPaste);

    basicStore.getBasicData().then(() => {
        if (is_valid(props.event)) {
            basicStore.getEvent(props.event);
        }
    });
});

onUnmounted(() => {
    document.removeEventListener('keydown', onTrackKey);
    document.removeEventListener('paste', onPaste);
})

function onPaste(event:any)
{
    event.preventDefault();
    let paste = (event.clipboardData || (window as any).clipboardData).getData("text");
    if (paste && paste.length) {
        data.processFullCode(paste);
    }
}

function onTrackKey(event:any)
{
    // allow composing values
    if (event.isComposing || event.keyCode === 229) {
        return;
    }
    // do not react to special-modifiers, so we can safely apply keyboard
    // shortcuts
    if (event.altKey || event.ctrlKey || event.metaKey) {
        return;
    }
    data.addCode(event.code, event.key);
}

const authStore = useAuthStore();

// This is the place to deal with changes in the login state
// of the user
// Each time we switch guest state to true, we retrieve a new CSRF token
watch(
    () => [authStore.isGuest, authStore.codeUser],
    (nw) => {
        if(nw[0] || !nw[1]) {
            authStore.sendMe().then(() => {
                // if the user is linked to a specific event, get that event now
                if (is_valid(authStore.eventId)) {
                    basicStore.getEvent(authStore.eventId);
                }
            });
        }
    },
    { immediate: true }
);

import DashboardView from './pages/DashboardView.vue';
import LoadingService from './components/special/LoadingService.vue';
import Overview from './components/OverviewBoard.vue';
</script>
<template>
    <div>
        <LoadingService/>
        <DashboardView v-if="!authStore.isOverview(authStore.eventId, 'code')"/>
        <Overview v-else :visible="true" />
    </div>
</template>
