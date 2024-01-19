<script lang="ts" setup>
import { ref, watch, computed } from 'vue';
import type { ElementContent, TemplateSchema } from '../../../../common/api/schemas/template';
import type { RoleSchema } from '../../../../common/api/schemas/role';
import type { StringKeyedStringList } from '../../../../common/types';
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { savetemplate } from '../../../../common/api/templates/savetemplate';
import { templateprint } from '../../../../common/api/templates/templateprint';
import { is_valid } from '../../../../common/functions';

const props = defineProps<{
    visible:boolean;
    template:TemplateSchema;
    fonts:StringKeyedStringList;
}>();
const emits = defineEmits(['onClose', 'onUpdate', 'onSave']);
const data = useDataStore();
const auth = useAuthStore();

function closeForm()
{
    emits('onClose');
}

function saveForm(doClose = true)
{
    auth.isLoading('savetemplate');
    return savetemplate(props.template)
        .then((dt) => {
            auth.hasLoaded('savetemplate');
            if (dt) {
                emits('onSave', dt);
                if (doClose) {
                    emits('onClose');
                }
            }
            else {
              throw new Error("Missing template in return data");
            }
        })
        .catch((e) => {
            console.log(e);
            auth.hasLoaded('savetemplate');
            alert('There was an unexpected network error. Please try again or reload the page');
        });
}

function update(fieldName:string, value: any)
{
    emits('onUpdate', {field: fieldName, value: value});
}

function updateContent(fieldDef:any)
{
    console.log(fieldDef);
    if (fieldDef.field == 'addElement') {
        let elements = props.template.content.elements?.slice() || [];
        elements.push(fieldDef.value);
        update('elements', elements);
    }
    else if(fieldDef.field == 'editElement') {
        if (props.template.content.elements) {
            let elements = props.template.content.elements.map((el:ElementContent) => {
                if (el.index == fieldDef.value.index) {
                    return fieldDef.value;
                }
                return el;
            });
            update('elements', elements);
        }
    }
    else if(fieldDef.field == 'deleteElement') {
        if (props.template.content.elements) {
            let elements = props.template.content.elements.filter((el:ElementContent) => el.index != fieldDef.value.index);
            update('elements', elements);
        }
    }
    else if (fieldDef.field == 'pictures') {
        update('pictures', fieldDef.value);
        saveForm(false);
    }
}

const printOptions = [{
        label: "A6, double, A4 portrait, 2-per-page",
        value: "a4portrait"
    }, {
        label: "A6, double, A4 landscape, 2-per-page",
        value: "a4landscape"
    }, {
        label: "A6, single, A4 portrait, 4-per-page",
        value: "a4portrait2"
    },{
        label: "A6, single, A4 landscape, centered, 2-per-page",
        value: "a4landscape2"
    },{
        label: "A6, double, A5 landscape, 1-per-page",
        value: "a5landscape"
    }, {
        label: "A6, single, A5 landscape, 2-per-page",
        value: "a5landscape2"
    }, {
        label: "A6, single, A6 portrait, 1-per-page",
        value: "a6portrait"
    }
];

const selectableRoles = computed(() => {
    let output:RoleSchema[] = [];
    output.push({id:0, name:'Athlete', type: '0'});
    data.roles.map((rl) => {
        output.push(rl);
    });
    return output;
});

function convertedContentRoles()
{
    // old templates may contain a list of string numbers
    let retval:number[] = [];
    let roles = props.template.content.roles || [];
    for(let v in roles) {
      retval.push(parseInt(roles[v]));
    }
    return retval;
}

function convertedContentPrint()
{
    return props.template.content.print || 'a4portrait';
}

function printForm()
{
    return saveForm(false)
        .then(() => {
            templateprint(props.template).catch((e) => {
                console.log(e);
                alert('There was an error retrieving the example print');
            });
        });
}

import DndEditor from './DndEditor.vue';
import { ElDialog, ElButton, ElForm, ElFormItem, ElSelect, ElOption, ElInput, ElCheckbox } from 'element-plus';
import { useData } from 'element-plus/es/components/table-v2/src/composables';
</script>
<template>
    <ElDialog width="80%" class='template-dialog' :model-value="props.visible" title="Template Editor" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <div class='template-editor-header'>
          <h3>{{ props.template.name }}</h3>
      </div>
      <ElForm>
          <ElFormItem label="Name">
              <ElInput :model-value="props.template.name" @update:model-value="(e) => update('name', e)"/>
          </ElFormItem>
          <ElFormItem label="Roles">
              <ElSelect
                  :model-value="convertedContentRoles()"
                  @update:model-value="(e) => update('roles', e)"
                  multiple
                  collapse-tags
                  collapse-tags-tooltip
                  :max-collapse-tags="4"
                  placeholder="Select">
                  <ElOption v-for="role in selectableRoles" :key="role.id" :value="role.id" :label="role.name" />
              </ElSelect>
          </ElFormItem>
          <ElFormItem label="Print">
              <ElSelect :model-value="convertedContentPrint()" @update:model-value="(e) => update('print', e)">
                  <ElOption v-for="(option, index) in printOptions" :key="index" :value="option.value" :label="option.label" />
              </ElSelect>
          </ElFormItem>
          <ElFormItem label="Default" v-if="auth.isSysop()">
              <ElCheckbox :model-value="props.template.isDefault == 'Y'" @update:model-value="(e) => update('isDefault', e)" label="Set as default template"/>
          </ElFormItem>
      </ElForm>
      <DndEditor :template="props.template" :fonts="props.fonts" @on-update="(e) => updateContent(e)"/>
      <template #footer>
          <span class="dialog-footer">
              <ElButton v-if="is_valid($props.template.id)" @click="printForm">Print</ElButton>
              <ElButton type="warning" @click="closeForm">Cancel</ElButton>
              <ElButton type="primary" @click="saveForm">Save</ElButton>
          </span>
      </template>
    </ElDialog>
</template>