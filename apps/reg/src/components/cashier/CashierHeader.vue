<script lang="ts" setup>
import { is_valid } from '../../../../common/functions';
import { useDataStore } from '../../stores/data';
const props = defineProps<{
    countrySwitch: boolean;
}>();
const data = useDataStore();

function updateCountry(id:string)
{
    data.setCountry(parseInt(id));
}

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="cashier-header">
        <div v-if="props.countrySwitch">
            <div class="country-switch">
                <label>Select a country:</label>
                <ElSelect :model-value="'' + data.currentCountry.id" @update:model-value="(e) => updateCountry(e)" size="small">
                    <ElOption value="0" label="Organisation"/>
                    <ElOption v-for="item in data.countries" :key="item.id" :value="'' + item.id" :label="item.name"/>
                </ElSelect>
            </div>
            <h3 class="text-center">Payment status for {{ data.currentCountry.name }}</h3>
        </div>
        <div v-else>
            <h3 class="text-center">Head of Delegation of <span v-if="is_valid(data.currentCountry)">{{ data.currentCountry.name }}</span></h3>
        </div>
    </div>
</template>