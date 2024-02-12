<script lang="ts" setup>
import { onMounted, onUnmounted } from 'vue';
import { useDataStore } from '../stores/data';

const data = useDataStore();

onMounted(() => {
    data.subtitle = '';
    data.setDispatcher('badge', data.adminDispatcher);
});
onUnmounted(() => {
    data.setDispatcher('badge', null);
});

import InputComponent from './InputComponent.vue';
</script>
<template>
    <div>
        <InputComponent/>
        <h3 class="textcenter">No event selected</h3>
        <p>There currently is no event selected for this application. To start, please activate
            the application by scanning one of the functional codes.<br/><br/>
            Please contact the
            <a href="mailto:webmaster@veteransfencing.eu">webmaster</a> if this seems
            to be an error.
        </p>
    </div>
</template>