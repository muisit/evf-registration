<script lang="ts" setup>
import {watch,  ref } from 'vue';
import { is_valid } from '../../../../common/functions';
const props = defineProps<{
    countrySwitch: boolean;
}>();
const emits = defineEmits(['onChangeCountry']);

import { useAuthStore } from '../../../../common/stores/auth';
import { useDataStore } from '../../stores/data';
const data = useDataStore();
const auth = useAuthStore();

const currentSelection = ref('0');
watch(
    () => currentSelection.value,
    (nw) => {
        var country = null;
        var cid = parseInt(nw);
        console.log("new country is ", nw);
        if (is_valid(cid) && data.countriesById['c' + cid]) {
            country = data.countriesById['c' + cid];
        }
        else {
            currentSelection.value = '0';
        }
        emits('onChangeCountry', {countryId: cid, country: country});
    }
)

function getSelectedName()
{
    var cid = parseInt(currentSelection.value);
    if (is_valid(cid) && data.countriesById['c' + cid]) {
        return data.countriesById['c' + cid].name
    }
    return "Organisation";
}

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="registration-header">
        <div v-if="countrySwitch">
            <div  class="country-switch">
                <label>Select a country:</label>
                <ElSelect v-model="currentSelection" size="small">
                    <ElOption value="0" label="Organisation"/>
                    <ElOption v-for="item in data.countries" :key="item.id" :value="item.id" :label="item.name"/>
                </ElSelect>
            </div>
            <h3 class="text-center">Registration for {{ getSelectedName() }}</h3>
        </div>
        <div v-else>
            <h3 class="text-center">Head of Delegation <span v-if="is_valid(auth.country)">{{ auth.country.name }}</span></h3>
        </div>
    </div>
</template>