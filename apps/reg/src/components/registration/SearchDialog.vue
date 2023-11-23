<script lang="ts" setup>
import { Ref, ref, watch } from 'vue';
import { defaultFencer, Fencer, FencerList } from '../../../../common/api/schemas/fencer';
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
                console.log('calling focus on ', searchbox.value);
                if (searchbox.value) searchbox.value.focus();
                let el = document.getElementById(searchbox.value?.input?.id);
                if (el) {
                    console.log('calling focus on ', searchbox.value?.input?.id, el);
                    el.focus();
                }
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
                console.log('testing ', name.value, 'vs ', autocompleteData.name);
                if (name.value == autocompleteData.name) {
                    autocomplete(autocompleteData)
                        .then((results) => {
                            if (autocompleteData.name == name.value) {
                                suggestions.value = results.map((fencer) => {
                                    console.log('fencer country is ', fencer.countryId);
                                    decorateFencer(fencer);
                                    console.log('fencer country is ', fencer.countryId);
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
                    <th>Gender</th>
                    <th>Date of birth</th>
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
                    <td>{{ fencer.fullGender }}</td>
                    <td>{{ fencer.dateOfBirth }}</td>
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