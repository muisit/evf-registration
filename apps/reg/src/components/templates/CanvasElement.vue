<script lang="ts" setup>
import { computed } from 'vue';
import type { Ref, StyleValue } from 'vue';
import type { ElementContent, TemplateSchema } from '../../../../common/api/schemas/template';
import { useDataStore } from '../../stores/data';

const props = defineProps<{
    element:ElementContent;
    template:TemplateSchema;
}>();
const emits = defineEmits(['onDragStart', 'onDelete']);
const data = useDataStore();

function onDragStart(e:any)
{
    emits('onDragStart', {event: e, element: props.element});
}

const styleObject:Ref<StyleValue> = computed(() => {
    let baseObject:StyleValue = {
        top: (props.element.style.top || 0) + 'px',
        left: (props.element.style.left || 0) + 'px',
        width: (props.element.style.width || 30) + 'px',
        height: (props.element.style.height || 18) + 'px',
        zIndex: props.element.style.zIndex || 1
    };
    if (props.element.hasFontSize) {
        baseObject.fontSize = props.element.style.fontSize ? (props.element.style.fontSize + 'px') : '1em';
        baseObject.fontFamily = props.element.style.fontFamily + ',Helvetica,Arial';
        baseObject.fontStyle = props.element.style.fontStyle || 'normal';
        baseObject.textAlign = props.element.style.textAlign || 'left';
    }
    if (props.element.hasColour) {
        baseObject.color = props.element.style.color || '#000';
    }
    if (props.element.hasBackgroundColour) {
        baseObject.backgroundColor = props.element.style.backgroundColor || '#000';
    }
    else {
        baseObject.backgroundColor = 'none';
    }
    if (props.element.type == 'img') {
        let url = import.meta.env.VITE_API_URL + "/templates/" + props.template.id  + "/picture/" + (props.element.file_id) + "?event=" + data.currentEvent.id;
        baseObject.backgroundImage = 'url(' + url + ')';
        baseObject.backgroundSize = 'contain';
    }
    return baseObject;
});

function hasText()
{
    return props.element.text && ['category', 'country', 'org', 'text'].includes(props.element.type);
}

const onedateonly = computed(() => {
    return props.element.onedateonly && props.element.onedateonly === true;
})

const nametext = computed(() => {
    let txt = "NOSUCHPERSON, nosuchname";
    if (props.element.name == "first") {
        txt = "nosuchname";
    }
    else if (props.element.name == "last") {
        txt = "NOSUCHPERSON";
    }
    return txt;
});

function deleteItem(event:any)
{
    if (confirm('Delete this element of type ' + props.element.type + '?')) {
        emits('onDelete');
    }
    event.preventDefault();
}

import { Delete, Rank } from '@element-plus/icons-vue';
import { ElIcon } from 'element-plus';
import EUFlag from '../../assets/euflag.png';
import PhotoID from '../../assets/photoid.png';
import QRCode from '../../assets/qrcode.png';
import ACCId from '../../assets/accid.png';
</script>
<template>
    <div class="canvas-element" :style="styleObject" draggable="true" @dragstart="onDragStart">
        <div class="canvas-element-content">
            <div v-if="props.element.type == 'qr'" class="qr">
                <img :src="QRCode"/>
            </div>
            <div v-if="props.element.type == 'accid'" class="id-code">
                <img :src="ACCId"/>
            </div>
            <div v-if="hasText()" class="text">{{ props.element.text }}</div>
            <div v-if="props.element.type == 'cntflag'" class="cntflag">
                <img :src="EUFlag"></div>
            <div v-if="props.element.type == 'dates'" class="dates">
                <span class="date">07 WED</span>
                <br v-if="!onedateonly"/>
                <span v-if="!onedateonly" class="date">21 SUN</span>
            </div>
            <div v-if="props.element.type == 'img'" class="image"></div>
            <div v-if="props.element.type == 'name'" class="text">{{ nametext }}</div>
            <div v-if="props.element.type == 'photo'" class="photo">
                <img :src="PhotoID"/>
            </div>
            <div v-if="props.element.type == 'roles'" class="text">Athlete WS4, Team Armourer,<br/>Head of Delegation, Referee</div>
            <ElIcon size="small" class="canvas-trash" @click="deleteItem">
                <Delete />
            </ElIcon>
        </div>
    </div>
</template>