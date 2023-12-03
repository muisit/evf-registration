<script lang="ts" setup>
import { ref } from 'vue';
import { useDataStore } from '../../stores/data';
const dataStore = useDataStore();

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div v-if="dataStore.events && dataStore.events.length > 1">
        <ElSelect :model-value="dataStore.currentEvent.id || 0" size="small" @update:model-value="(e) => dataStore.setEvent(parseInt(e))">
            <ElOption v-for="item in dataStore.events" :key="item.id || 0" :value="item.id || '0'" :label="item.name || ''"/>
        </ElSelect>
    </div>
</template>