<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { Fencer, FencerList } from '../../../../common/api/schemas/fencer';
import { defaultFencer } from '../../../../common/api/schemas/fencer';
import { useDataStore } from '../../stores/data';
import { autocomplete } from '../../../../common/api/fencers/autocomplete';
import { filterSuggestionsFromFencerList } from './lib/filterSuggestionsFromFencerList';
import { decorateFencer } from '../../stores/lib/decorateFencer';
import { is_valid } from '../../../../common/functions';
import type { InputInstance } from 'element-plus'
const props = defineProps<{
    visible:boolean;
    fencers:FencerList;
}>();
const emits = defineEmits(['onClose','onSave']);

const data = useDataStore();
const name = ref('');
const suggestions:Ref<FencerList> = ref([]);
const searchbox:Ref<InputInstance|null> = ref(null);

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            // allow rendering to settle, then focus
            // this did not work with a mere nextTick
            window.setTimeout(() => {
                if (searchbox.value) searchbox.value.focus();
            }, 10);
        }
    }
);

function closeForm()
{
    suggestions.value = [];
    name.value = '';
    emits('onClose');
}

function createNewFencer()
{
    var fencer = defaultFencer();
    fencer.lastName = name.value.toUpperCase();
    fencer.countryId = is_valid(data.currentCountry) ? data.currentCountry.id : data.countries[0].id;
    emits('onSave', fencer);
    closeForm();
}

function selectFencer(fencer:Fencer)
{
    emits('onSave', fencer);
    closeForm();
}


function onChange()
{
    if(name.value.length > 1) {
        if (!props.fencers || props.fencers.length == 0) {
            var autocompleteData = {
                name: name.value,
                country: data.currentCountry.id
            };

            window.setTimeout(() => {
                if (name.value == autocompleteData.name && data.currentCountry.id == autocompleteData.country) {
                    autocomplete(name.value)
                        .then((results) => {
                            if (autocompleteData.name == name.value) {
                                suggestions.value = results.map((fencer) => {
                                    decorateFencer(fencer);
                                    return fencer;
                                });
                            }
                            else {
                                console.log('skipping setting suggestions');
                            }
                        });
                    }
                }, 500);
        }
        else {
            suggestions.value = filterSuggestionsFromFencerList(name.value, props.fencers);
        }
    }
    else {
        suggestions.value = [];
    }
}

watch (
    () => name.value,
    () => onChange()
);


import { ElDialog, ElInput, ElButton } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Search Fencer" :close-on-click-modal="false" :before-close="(done) => { closeForm(); done(false); }">
        <ElInput v-model="name" ref="searchbox"/>

        <table v-if="name.length>1" class="suggestion-list">
            <thead>
                <tr>
                    <th>Lastname</th>
                    <th>Firstname</th>
                    <th v-if="is_valid(data.currentCountry.id)">Gender</th>
                    <th v-if="is_valid(data.currentCountry.id)">Date of birth</th>
                    <th v-if="!is_valid(data.currentCountry.id)">Country</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">
                        <ElButton type="warning" @click="createNewFencer">Create new entry</ElButton>
                    </td>
                </tr>
                <tr v-for="fencer in suggestions" :key="fencer.id">
                    <td>{{ fencer.lastName?.toUpperCase() }}</td>
                    <td>{{ fencer.firstName }}</td>
                    <td v-if="is_valid(data.currentCountry.id)">{{ fencer.fullGender }}</td>
                    <td v-if="is_valid(data.currentCountry.id)">{{ fencer.dateOfBirth }}</td>
                    <td v-if="!is_valid(data.currentCountry.id)">{{ fencer.country?.name || 'Unknown' }}</td>
                    <td>
                        <ElButton type="primary" @click="() => selectFencer(fencer)">select</ElButton>
                    </td>
                </tr>
            </tbody>
        </table>
        <template #footer>
            <span class="dialog-footer">
            <ElButton type="warning" @click="closeForm">Cancel</ElButton>
            </span>
        </template>
    </ElDialog>
</template>