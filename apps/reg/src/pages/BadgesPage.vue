<script lang="ts" setup>
import { watch } from 'vue';
import { useDataStore } from '../stores/data';
import { useAccreditationsStore } from '../stores/accreditations';
const props = defineProps<{
    visible:boolean;
}>();
const data = useDataStore();
const accreditations = useAccreditationsStore();

watch(
    () => [props.visible, data.currentEvent],
    () => {
        if (props.visible) {
            accreditations.getAccreditationData();
        }
    },
    { immediate: true }
)

import BadgeOverview from '../components/badges/BadgeOverview.vue';
</script>
<template>
    <div class="badges-page" v-if="props.visible">
        <div class="badges-header">
            <h2>Accreditation Badge Overview</h2>
        </div>
        <div class="badges-list">
            <BadgeOverview />
        </div>
        <div class="badges-footer">

        </div>
    </div>
</template>
