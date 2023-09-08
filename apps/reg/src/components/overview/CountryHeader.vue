<script lang="ts" setup>
import { useDataStore } from '../../stores/data';
const data = useDataStore();
import { ElTooltip } from 'element-plus';
</script>
<template>
    <thead>
        <tr>
        <th class="text-left">Country</th>
        <th v-for="se in data.competitionEvents" :key="se.id" class='text-right'>
            <ElTooltip :content="se.title">
                {{ se.abbr }}
            </ElTooltip>
        </th>
        <th class="text-right">
            <ElTooltip content="Total number of participating athletes">
                Tot
            </ElTooltip>
        </th>
        <th v-for="se in data.nonCompetitionEvents" :key="se.id" class='text-right'>
            <ElTooltip :content="se.title">
                {{ se.abbr }}
            </ElTooltip>
        </th>
        <th class='text-right'>
            <ElTooltip content="Federative support roles like Coach, Head of Delegation">
                Sup
            </ElTooltip>
        </th>
        </tr>
    </thead>
</template>