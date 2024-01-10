<script lang="ts" setup>
const props = defineProps<{
    visible:boolean;
}>();

import ParticipantOverview from '../components/participants/ParticipantOverview.vue';
</script>
<template>
    <div class="participants-page" v-if="props.visible">
        <div class="participants-header">
            <h2>Participant Lists</h2>
        </div>
        <div class="participants-list">
            <ParticipantOverview/>
        </div>
        <div class="participants-footer">

        </div>
    </div>
</template>
