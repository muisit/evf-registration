<script lang="ts" setup>
import type { ElementContent, PictureContent, TemplateSchema } from '../../../../common/api/schemas/template';
import { elementFactory } from './lib/elementFactory';

const props = defineProps<{
    template: TemplateSchema;
    elements:ElementContent[];
    pictures:PictureContent[];
}>();
const emits = defineEmits(['onAdd', 'onUpdate', 'onSelect', 'onDelete']);

function onDrop(e:any)
{
    let target = e.target;
    while (target && !target.className.includes('dropzone')) target = target.parentElement;

    if (target && target.className.includes("dropzone")) {
        var rect = target.getBoundingClientRect();
        let val:number = e.dataTransfer.getData('value');
        let tbox:string = e.dataTransfer.getData('toolbox');
        let x:number = e.dataTransfer.getData('offsetX') || 0;
        let y:number = e.dataTransfer.getData('offsetY') || 0;
        if (val && !tbox) {
            if (props.elements) {
                let elements = props.elements.slice().map((el:ElementContent) => {
                    if (el.index == val) {
                        el.style.top = e.clientY - y - rect.top;
                        el.style.left = e.clientX - x - rect.left;
                        if (el.style.top < 0) el.style.top = 0;
                        if (el.style.left < 0) el.style.left = 0;
                        if (el.style.width > 0 && (el.style.width+el.style.left) > 420) {
                            el.style.left = 420 - el.style.width;
                        }
                        if (el.style.height > 0 && (el.style.height + el.style.top) > 594) {
                            el.style.top = 594 - el.style.height;
                        }
                    }
                    return el;
                });
                emits('onUpdate', {field: 'elements', elements})
            }
        }
        else if(tbox && !val) {
            let pictureId:string = e.dataTransfer.getData('pictureId');
            console.log('matching ', pictureId);
            let picture = props.pictures?.find((p) => p.file_id == pictureId);
            let index = -1;
            props.elements.map((el) => { if (el.index > index) index = el.index; })
            let element = elementFactory(e.clientX - x - rect.left, e.clientY - y - rect.top, index + 2, tbox, props.template, picture);
            emits('onAdd', element);
        }
    }
}

function onStart(data:any)
{
    emits('onSelect', data.element);
    console.log(data.event.target);
    var rect = data.event.target.getBoundingClientRect();
    console.log('item selected from canvas (' + data.event.clientX + ',' + data.event.clientY + ') - (' + rect.left + ',' + rect.top + ') => (' + (data.event.clientX - rect.left) + ',' + (data.event.clientY - rect.top) + ')');
    data.event.dataTransfer.dropEffect = 'move';
    data.event.dataTransfer.effectAllowed = 'move';
    data.event.dataTransfer.setData('value', data.element.index);
    data.event.dataTransfer.setData('offsetX', data.event.clientX - rect.left);
    data.event.dataTransfer.setData('offsetY', data.event.clientY - rect.top);
}

function onSelect(value:ElementContent)
{
    emits('onSelect', value);
}

function deleteElement(value:ElementContent)
{
    emits('onDelete', value);
}

import CanvasElement from './CanvasElement.vue';
</script>
<template>
    <div class="canvas-wrapper dropzone" @drop="onDrop" @dragenter.prevent @dragover.prevent>
        <div class="canvas-margins"></div>
        <div class="canvas"></div>
        <CanvasElement v-for="(element, i) in props.elements" :key="i" :template="template" :element="element" @on-drag-start="onStart" @click="() => onSelect(element)" @on-delete="() => deleteElement(element)"/>
    </div>
</template>