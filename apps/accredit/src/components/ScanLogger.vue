<script lang="ts" setup>
import { computed } from 'vue';
import { useDataStore } from '../stores/data';

const logList = computed(() => {
    const data = useDataStore();
    let lst = data.processingList.slice();
    return lst.reverse();
});
</script>
<template>
    <div class="scan-logger">
        <div v-for="(code, i) in logList" :key="i">
            <span :class="{code: true, error: !code.baseFunction}">{{ code.scannedTime }}: {{ code.original }}</span>
        </div>
    </div>
</template>