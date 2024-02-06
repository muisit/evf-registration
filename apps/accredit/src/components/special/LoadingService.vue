<script lang="ts" setup>
import { useDataStore } from '../../stores/data';
const data = useDataStore();

import { ElIcon } from 'element-plus';
import { Loading } from '@element-plus/icons-vue';
</script>
<template>
    <div class="loading-screen" v-if="data.isCurrentlyLoading()">
        <div class='wrapper'></div>
        <div class='content'>
            <div class="backdrop"></div>
            <div class="text">
                <h3>Loading</h3>
                <ElIcon size="xx-large" class="is-loading"><Loading /></ElIcon>
                <p>Please wait while loading data</p>
            </div>
        </div>
    </div>
</template>