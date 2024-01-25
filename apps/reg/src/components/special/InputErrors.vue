<script lang="ts" setup>
const props = defineProps<{
    messages:string[];
}>();

</script>
<template>
    <div class="messages">
        <div v-for="(msg, i) in props.messages" :key="i">{{ msg }}</div>
    </div>
</template>
