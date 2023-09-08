<script lang="ts" setup>
import { ref } from 'vue';
import { useDataStore } from '../../stores/data';
const dataStore = useDataStore();

const currentSelection = ref(dataStore.currentEvent.id);

function changeSelectedEvent() {
    dataStore.setEvent(currentSelection.value);
}

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div v-if="dataStore.events && dataStore.events.length > 1">
        <ElSelect v-model="currentSelection" size="small" @change="changeSelectedEvent">
            <ElOption v-for="item in dataStore.events" :key="item.id" :value="item.id" :label="item.name"/>
        </ElSelect>
    </div>
</template>