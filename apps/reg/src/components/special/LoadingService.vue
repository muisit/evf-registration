<script lang="ts" setup>
import { watch } from 'vue'
import { useAuthStore } from '../../../../common/stores/auth';
import { ElLoading } from 'element-plus'

const auth = useAuthStore();

watch(
    () => auth.isLoading,
    (nw) => {
        if (nw) {
            ElLoading.service({
                fullscreen: true,
                lock: true,
                text: 'Please wait while loading data',
                background: 'rgba(0, 0, 0, 0.3)',
            });
        }
        else {
            const loading = ElLoading.service({ fullscreen: true });
            loading.close();
        }
    }
);
</script>
<template>
    <div></div>
</template>