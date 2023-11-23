<script lang="ts" setup>
import { ref } from 'vue';
import { SideEvent } from "../../../../common/api/schemas/sideevent";
import { Registration } from '../../../../common/api/schemas/registration';
import { useDataStore } from "../../stores/data";
import { format_date_fe_short, random_token, is_valid } from  '../../../../common/functions';

const props = defineProps<{
    event:SideEvent;
    registration:Registration|null;
    teams:string[];
}>();
const emits = defineEmits(['onUpdate']);

const data = useDataStore();

function allowMoreTeams()
{
    if (data.currentEvent && data.currentEvent.config && data.currentEvent.config.allow_more_teams) {
        return true;
    }
    return false;
}

function inputDisabled()
{
    if (props.registration?.state && props.registration?.state != 'saved') return true;
    return false;
}

function checkboxValue()
{
    return props.registration && is_valid(props.registration.id) ? true : false;
}

const checkbox = ref(random_token(32));

function update(selection)
{
    if (props.event.competition?.category?.type == 'T') {
        if (allowMoreTeams()) {
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
            console.log('team selection says ', selection, selection || '');
            emits('onUpdate', selection || '');
        }
        else {
            emits('onUpdate', selection ? 'Team 1' : null);
        }
    }
    else {
        emits('onUpdate', selection ? true : null);
    }
}
import { Select, CloseBold, Upload } from '@element-plus/icons-vue';
import { ElCheckbox, ElSelect, ElOption, ElIcon } from 'element-plus';
</script>
<template>
    <tbody>
        <tr>
            <td>
                <ElCheckbox v-if="!allowMoreTeams()" :model-value="checkboxValue()" @update:model-value="update" :disabled="inputDisabled()" :id="checkbox"/>
                <ElSelect v-if="allowMoreTeams()" :model-value="props.registration?.team || ''" @update:model-value="update" :disabled="inputDisabled()">
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
            <td :class="{'state-icons':true, 'state-error': props.registration?.state == 'error', 'state-upload': props.registration?.state == 'saving', 'state-ok': props.registration?.state == 'saved'}">
                <ElIcon>
                    <CloseBold v-if="props.registration?.state == 'error'" />
                    <Select v-if="props.registration?.state == 'saved'"/>
                    <Upload v-if="props.registration?.state == 'saving'"/>
                </ElIcon>
            </td>
            <td class="filler-cell"></td>
        </tr>
        <tr v-if="props.event.description && props.event.description.length > 0" class="sideevent-description">
            <td colspan="5">{{ props.event.description }}</td>
        </tr>
    </tbody>
</template>