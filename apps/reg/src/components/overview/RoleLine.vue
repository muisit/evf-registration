<script lang="ts" setup>
const props = defineProps<{
    line:any;
}>();
</script>
<template>
    <tr v-if="line.role && line.count">
        <td class="text-left">{{ line.role.name }}</td>
        <td class="text-right">{{ line.count }}</td>
    </tr>
</template>