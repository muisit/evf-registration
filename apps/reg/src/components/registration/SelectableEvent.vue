<script lang="ts" setup>
import { ref, computed } from 'vue';
import type { Ref } from 'vue';
import type { SideEvent } from "../../../../common/api/schemas/sideevent";
import type { Registration } from '../../../../common/api/schemas/registration';
import type { Fencer } from '../../../../common/api/schemas/fencer';
import { defaultRegistration } from '../../../../common/api/schemas/registration';
import { useDataStore } from "../../stores/data";
import { format_date_fe_short, random_token, is_valid } from  '../../../../common/functions';
import { allowMoreTeams } from '../../../../common/lib/event';

const props = defineProps<{
    event:SideEvent;
    fencer:Fencer;
    teams:string[];
}>();
const emits = defineEmits(['update']);

const data = useDataStore();

const registration:Ref<Registration> = computed(() => {
    if (props.fencer.registrations) {
        for(let i in props.fencer.registrations) {
            let reg = props.fencer.registrations[i];
            if (reg.sideEventId == props.event.id) {
                return reg;
            }
        }
    }
    return defaultRegistration();
});


function inputDisabled()
{
    if (['error', 'saving', 'removing'].includes(registration.value.state || '')) return true;
    return false;
}

function checkboxValue()
{
    if (  (is_valid(registration.value.id) && ['', 'saved','saving'].includes(registration.value.state || ''))
       || (!is_valid(registration.value.id) && registration.value.state == 'saving')
    ) {
        return true;
    }
    return false;
}

const checkbox = ref(random_token(32));

function update(selection:any)
{
    if (props.event.competition?.category?.type == 'T') {
        if (allowMoreTeams(data.currentEvent)) {
            if (selection == 'new') {
                let found = true;
                let index = 0;
                while (found) {
                    found = false;
                    index += 1;
                    selection = 'Team ' + index;
                    props.teams.map((teamName) => {
                        if (teamName == selection) {
                            found = true;
                        }
                    });
                }
            }
            console.log('updating with team selection', selection);
            emits('update', selection || '');
        }
        else {
            console.log('updating for single team Team 1');
            emits('update', selection ? 'Team 1' : null);
        }
    }
    else {
        console.log('updating individual event');
        emits('update', selection ? true : null);
    }
}

const hasError = computed(() => registration.value.state == 'error');
const isSaving = computed(() => registration.value.state == 'saving' || registration.value.state == 'removing');
const isSaved = computed(() => registration.value.state == 'saved' || registration.value.state == 'removed');

import { Select, CloseBold, Upload } from '@element-plus/icons-vue';
import { ElCheckbox, ElSelect, ElOption, ElIcon } from 'element-plus';
</script>
<template>
    <tbody>
        <tr>
            <td>
                <ElCheckbox v-if="!allowMoreTeams(data.currentEvent)" :model-value="checkboxValue()" @update:model-value="update" :disabled="inputDisabled()" :id="checkbox"/>
                <ElSelect v-if="allowMoreTeams(data.currentEvent)" :model-value="registration.team || ''" @update:model-value="update" :disabled="inputDisabled()">
                    <ElOption value="" label="Select a team"/>
                    <ElOption v-for="(team,i) in props.teams" :key="i" :value="team" :label="team"/>
                    <ElOption value="new" label="New team"/>
                </ElSelect>
            </td>
            <td>
                <label :for="checkbox">
                    {{ format_date_fe_short(props.event.starts) }}
                </label>
            </td>
            <td>
                <label :for="checkbox">
                    {{ props.event.title }}
                </label>
            </td>
            <td :class="{'state-icons':true, 'state-error': hasError, 'state-upload': isSaving, 'state-ok': isSaved}">
                <ElIcon>
                    <CloseBold v-if="hasError" />
                    <Select v-if="isSaved"/>
                    <Upload v-if="isSaving"/>
                </ElIcon>
            </td>
            <td class="filler-cell"></td>
        </tr>
        <tr v-if="props.event.description && props.event.description.length > 0" class="sideevent-description">
            <td colspan="5">{{ props.event.description }}</td>
        </tr>
    </tbody>
</template>