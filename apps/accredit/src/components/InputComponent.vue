<script lang="ts" setup>
import { useDataStore } from '../stores/data';
const data = useDataStore();

</script>
<template>
    <div class="input-component" v-if="data.inputValue.length > 0">
        <div class="field">{{ data.inputValue }}</div>
    </div>
</template>