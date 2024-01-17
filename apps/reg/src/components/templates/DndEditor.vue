<script lang="ts" setup>
import { ref } from 'vue';
import type { Ref } from 'vue';
import type { ElementContent, PictureContent, TemplateSchema } from "../../../../common/api/schemas/template";
import type { StringKeyedStringList } from '../../../../common/types';
const props = defineProps<{
    template:TemplateSchema;
    fonts:StringKeyedStringList;
}>();
const emits = defineEmits(['onUpdate', 'onEdit']);

function update(field:string, value:any)
{
    emits('onUpdate', {field: field, value: value});
}

function addElement(e:ElementContent)
{
    emits('onUpdate', {field: 'addElement', value: e});
    selectedElement.value = e;
}

function updateElement(field:string, value: any)
{
    let element = Object.assign({}, selectedElement.value);
    switch (field) {
        case 'text': element.text = value; break;
        case 'left': element.style.left = parseInt(value); break;
        case 'top': element.style.top = parseInt(value); break;
        case 'width': element.style.width = parseInt(value); break;
        case 'height': 
            element.style.height = parseInt(value);
            if (element.hasRatio && element.ratio && !isNaN(element.ratio)) {
                element.style.width = element.style.height * element.ratio;
            }
            break;
        case 'zIndex': element.style.zIndex = parseInt(value); break;
        case 'fontSize': element.style.fontSize = parseInt(value); break;
        case 'fontFamily': element.style.fontFamily = value; break;
        case 'fontStyle': element.style.fontStyle = value; break;
        case 'textAlign': element.style.textAlign = value; break;
        case 'name': element.name = value; break;
        case 'onedateonly': element.onedateonly = value == 'Y'; break;
        case 'side': element.side = value; break;
        case 'link': element.link = value; break;
        case 'colour': element.style.color = value; break;
        case 'backgroundColour': element.style.backgroundColor = value; break;
    }
    emits('onUpdate', {field: 'editElement', value: element});
    selectedElement.value = element;
}

const toolboxData:any = ref({});
function createElement(element)
{
    toolboxData.value = element;
}

const selectedElement:Ref<ElementContent> = ref({type:'none', style:{}, index:0});
function selectElement(el:ElementContent)
{
    selectedElement.value = el;
}

function deleteElement(el:ElementContent)
{
    if (selectedElement.value.index == el.index) {
        selectedElement.value = {type:'none', style:{}, index:0};
    }
    emits('onUpdate', {field: 'deleteElement', value: el});
}

import ToolBox from './ToolBox.vue';
import Canvas from "./Canvas.vue";
import StyleEditor from './StyleEditor.vue';
</script>
<template>
    <div class="template-dnd-editor">
        <ToolBox :template="props.template" @on-select="createElement" @on-update="(e) => update(e.field, e.value)"/>
        <Canvas :elements="props.template.content.elements || []" :template="props.template" :pictures="props.template.content.pictures" @on-add="addElement" @on-update="(e) => update(e.field, e.value)" @on-select="selectElement" @on-delete="deleteElement"/>
        <StyleEditor :element="selectedElement" :fonts="props.fonts" @on-update="(e) => updateElement(e.field, e.value)"/>
    </div>
</template>