<script lang="ts" setup>
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
const props = defineProps<{
    visible:boolean;
}>();
const auth = useAuthStore();
const data = useDataStore();

</script>
<template>
    <div class="admin-interface" v-if="auth.isAccreditor()">

    </div>
</template>