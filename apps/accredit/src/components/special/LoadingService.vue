<script lang="ts" setup>
import { useAuthStore } from '../../../../common/stores/auth';
const auth = useAuthStore();

import { ElIcon } from 'element-plus';
import { Loading } from '@element-plus/icons-vue';
</script>
<template>
    <div class="loading-screen" v-if="auth.isCurrentlyLoading()">
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