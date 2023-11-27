<script lang="ts" setup>
import { hasTeam } from '../../../../common/lib/event';
import { useDataStore } from '../../stores/data';

const data = useDataStore();

import TeamList from './TeamList.vue';
import IndividualList from './IndividualList.vue';
</script>
<template>
    <div class="participant-list">
        <TeamList />
        <IndividualList />
    </div>
</template>
