<script lang="ts" setup>
import { useAuthStore } from '../../common/stores/auth';
const authStore = useAuthStore();

import DashboardView from './pages/DashboardView.vue';
</script>
<template>
    <DashboardView/>
</template>
