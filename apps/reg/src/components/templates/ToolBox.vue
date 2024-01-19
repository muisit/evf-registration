<script lang="ts" setup>
import { computed } from "vue";
import type { PictureContent, TemplateSchema } from "../../../../common/api/schemas/template";
import { uploadpicture } from '../../../../common/api/templates/uploadpicture';
import { removepicture } from "../../../../common/api/templates/removepicture";
const props = defineProps<{
    template:TemplateSchema;
}>();
const emits = defineEmits(['onSelect', 'onUpdate', 'onDelete']);

function select(event:any, value:any)
{
    var rect = event.target.getBoundingClientRect();
    console.log('item selected from toolbox (' + event.clientX + ',' + event.clientY + ') - (' + rect.left + ',' + rect.top + ') => (' + (event.clientX - rect.left) + ',' + (event.clientY - rect.top) + ')');
    emits('onSelect', value);
    event.dataTransfer.dropEffect = 'copy';
    event.dataTransfer.effectAllowed = 'copy';
    event.dataTransfer.setData('toolbox', value.name);
    event.dataTransfer.setData('pictureId', value.name == 'img' ? value.picture.file_id : null);
    event.dataTransfer.setData('offsetX', event.clientX - rect.left);
    event.dataTransfer.setData('offsetY', event.clientY - rect.top);
}

function availablePictures()
{
    return props.template.content.pictures || [];
}

function onFileChange(event:any)
{
    if (event.target.files && event.target.files.length) {
        uploadpicture(props.template, event.target.files[0])
            .then((picture) => {
                if (picture) {
                    let pictures = props.template.content.pictures?.slice() || [];
                    pictures.push(picture);
                    console.log('emitting onUpdate for pictures');
                    emits('onUpdate', {field: 'pictures', value: pictures});
                }
            });
    }
}

const toolboxlist = computed(() => {
    let lst:any[] = [];
    lst.push({name: 'photo'});
    lst.push({name: 'name'});
    lst.push({name: 'category'});
    lst.push({name: 'country'});
    lst.push({name: 'cntflag'});
    lst.push({name: 'org'});
    lst.push({name: 'roles'});
    lst.push({name: 'dates'});
    lst.push({name: 'text'});
    lst.push({name: 'qr'});
    lst.push({name: 'accid'});
    lst.push({name: 'box'});

    let pics = availablePictures();
    for (let i in pics) {
        lst.push({name: 'img', picture:pics[i]});
    }
    return lst;
})

function deletePicture(picture:PictureContent)
{
    if (confirm('Delete this image permanently from storage?')) {
        removepicture(props.template, picture)
            .then((dt) => {
                if (dt?.status != 'ok') {
                    alert('Something went wrong while deleting the image. Please save the template and reload the page');
                }
                else {
                    let pictures = (props.template.content.pictures?.slice() || []).filter((p:PictureContent) => p.file_id != picture.file_id);
                    emits('onUpdate', {field: 'pictures', value: pictures});
                }
            })
            .catch((e) => {
                console.log(e);
                alert('Something went wrong while deleting the image. Please save the template and reload the page');
            });
    }
}

import ToolBoxLine from "./ToolBoxLine.vue";
</script>
<template>
    <div class="toolbox">
        <ToolBoxLine v-for="(item, i) in toolboxlist" :key="i" 
            :type="item.name"
            :picture="item.picture"
            draggable="true"
            @dragstart="(e) => select(e, item)"
            @on-delete="() => deletePicture(item.picture)"
        />
        <div>
            <label for="pictureupload" class="picture-upload el-button el-button--primary">
                Upload Image
            </label>
            <input id='pictureupload' type="file" @change="onFileChange"/>
        </div>
    </div>
</template>