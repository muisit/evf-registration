<script lang="ts" setup>
import { ref, watch, computed } from 'vue';
import type { Ref } from 'vue';
import type { StringKeyedStringList } from '../../../common/types';
import { templatelist } from '../../../common/api/templates/templatelist';
import { fontlist } from '../../../common/api/templates/fontlist';
import { savetemplate, copytemplate } from '../../../common/api/templates/savetemplate';
import { removetemplate } from '../../../common/api/templates/removetemplate';
import { useDataStore } from '../stores/data';
import { useAuthStore } from '../../../common/stores/auth';
import type { TemplateSchema } from '../../../common/api/schemas/template';
const props = defineProps<{
    visible:boolean;
}>();

const auth = useAuthStore();
const data = useDataStore();
const templates:Ref<Array<TemplateSchema>> = ref([]);
const fonts:Ref<StringKeyedStringList> = ref({});
const selectedTemplate:Ref<TemplateSchema|null> = ref(null);
const showDialog = ref(false);

watch(() => [props.visible, data.currentEvent],
    (nw) => {
        if (nw[0]) {
            templatelist().then((dt) => {
                templates.value = sortTemplateList(dt);
            });
            fontlist().then((dt) => {
                fonts.value = dt;
            })
        }
    }
);

function sortTemplateList(lst:TemplateSchema[])
{
    return lst.sort((a, b) => {
        if (a.isDefault != b.isDefault) {
            return a.isDefault == 'Y' ? 1 : -1;
        }
        return a.name > b.name ? 1 : -1;
    });
}

function addToTemplateList(template:TemplateSchema)
{
    let lst = templates.value.slice();
    lst.push(template);
    templates.value = sortTemplateList(lst);
}

function editTemplate(template:TemplateSchema)
{
    selectedTemplate.value = Object.assign({}, template);
    showDialog.value = true;
}

function copyTemplate(template:TemplateSchema)
{
    auth.isLoading('copytemplate');
    copytemplate(template).then((newTemplate) => {
        auth.hasLoaded('copytemplate');
        if (newTemplate) {
            addToTemplateList(newTemplate);
        }
    })
    .catch((e) => {
        console.log(e);
        auth.hasLoaded('copytemplate');
        alert("There was an unexpected network error. Please try again or reload the page");
    });
}

function deleteTemplate(template:TemplateSchema)
{
    if (confirm('Are you sure you want to delete the template \'' + template.name + '\'?')) {
        auth.isLoading('deletetemplate');
        removetemplate(template).then((dt) => {
            if (dt?.status != 'ok') {
                alert('There was an error deleting the template. Please reload the page and try again');
            }
            templatelist().then((dt) => {
                auth.hasLoaded('deletetemplate');
                templates.value = sortTemplateList(dt);
            });
        })
        .catch((e) => {
            auth.hasLoaded('deletetemplate');
            console.log(e);
            alert('There was an error deleting the template. Please reload the page and try again');
        })
    }
}


function createNewTemplate()
{
    let template:TemplateSchema = {
        id: 0,
        name: 'New Template',
        content: {},
        eventId: data.currentEvent.id || 0,
        isDefault: 'N'
    };
    auth.isLoading('savetemplate');
    savetemplate(template).then((newTemplate) => {
        auth.hasLoaded('savetemplate');
        if (newTemplate) {
            addToTemplateList(newTemplate);
        }
    })
    .catch((e) => {
        console.log(e);
        auth.hasLoaded('savetemplate');
        alert("There was an unexpected network error. Please try again or reload the page");
    });
}

function dialogClose()
{
    showDialog.value = false;
    selectedTemplate.value = null;
}

function dialogSave(dt:TemplateSchema)
{
    // use the object as returned by the back-end to avoid local differences
    let lst = templates.value.slice();
    lst = lst.map((tmp) => {
        if (tmp.id == dt.id) {
            return dt;
        }
        return tmp;
    })
    templates.value = sortTemplateList(lst);
}

function dialogUpdate(fieldDef: any)
{
    console.log('update ', fieldDef);
    if (selectedTemplate.value) {
        let newValue = Object.assign({}, selectedTemplate.value);
        switch (fieldDef.field)
        {
            case 'print':
                newValue.content.print = fieldDef.value;
                break;
            case 'name':
                newValue.name = fieldDef.value;
                break;
            case 'roles':
                newValue.content.roles = fieldDef.value;
                break;
            case 'elements':
                newValue.content.elements = fieldDef.value;
                break;
            case 'pictures':
                newValue.content.pictures = fieldDef.value;
                break;
            case 'isDefault':
                newValue.isDefault = fieldDef.value ? 'Y' : 'N';
                break;
        }
        selectedTemplate.value = newValue;
    }
}

import TemplateEditorDialog from '../components/templates/TemplateEditorDialog.vue';
import { Edit, CopyDocument, Delete } from '@element-plus/icons-vue';
import { ElIcon, ElButton } from 'element-plus';
</script>
<template>
    <div class="templates-page" v-if="props.visible">
        <h3>Available Templates</h3>
        <table class="template-list">
            <tr v-for="template in templates" :key="template.id" :class="{is_default: template.isDefault == 'Y'}">
                <td class='name'>{{ template.name }}</td>
                <td>
                    <ElIcon size="large" @click="() => editTemplate(template)" v-if="auth.isSysop()">
                        <Edit />
                    </ElIcon>
                </td>
                <td>
                    <ElIcon size="large" @click="() => copyTemplate(template)">
                        <CopyDocument />
                    </ElIcon>
                </td>
                <td>
                    <ElIcon size="large" @click="() => deleteTemplate(template)" v-if="auth.isSysop() && template.isDefault == 'N'">
                        <Delete />
                    </ElIcon>
                </td>
            </tr>
        </table>
        <div class="templates-footer">
            <ElButton type="primary" v-if="auth.isSysop()" @click="createNewTemplate">Create</ElButton>
        </div>
        <TemplateEditorDialog v-if="selectedTemplate != null" :visible="showDialog" :fonts="fonts" :template="selectedTemplate" @on-close="dialogClose" @on-save="dialogSave" @on-update="dialogUpdate" />
    </div>
</template>