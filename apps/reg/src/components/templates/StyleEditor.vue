<script lang="ts" setup>
import { computed } from 'vue';
import type { ElementContent } from '../../../../common/api/schemas/template';
import type { StringKeyedStringList } from '../../../../common/types';

const props = defineProps<{
    element:ElementContent;
    fonts:StringKeyedStringList;
}>();
const emits = defineEmits(['onUpdate']);

function update(field:string, value:string)
{
    emits('onUpdate', {field:field, value:value});
}

const fontvaluelist = computed(() => {
    let retval:any = [];
    for(let i in props.fonts) {
        retval.push({name: i, value:props.fonts[i]});
    }
    return retval;
});

const maxHeight = computed(() => {
    let maxheight = 594;
    if (props.element.hasRatio && !isNaN(props.element.ratio || 1)) {
        let ratio = props.element.ratio || 1;
        maxheight = 420 / ratio;
        if (maxheight > 594) {
            maxheight = 594;
        }
    }
    return maxheight;
});

const maxWidth = computed(() => {
    let maxwidth = 420;
    if (props.element.hasRatio && !isNaN(props.element.ratio || 1)) {
        let ratio = props.element.ratio || 1;
        maxwidth = 420 * ratio;
        if (maxwidth > 420) {
            maxwidth = 420;
        }
    }
    return maxwidth;
});

import { ElForm, ElFormItem, ElInput, ElInputNumber, ElSelect, ElOption, ElColorPicker, ElRadio, ElRadioGroup, ElCheckbox } from 'element-plus';
</script>
<template>
    <div class="canvas-style-editor">
        <ElForm>
            <h3>{{ props.element.type }}</h3>
            <ElFormItem label="X">
                <ElInputNumber :min='0' :max='420' :model-value="props.element.style.left || 0" @update:model-value="(e) => update('left', '' + e)"/>
            </ElFormItem>
            <ElFormItem label="Y">
                <ElInputNumber :min='0' :max='594' :model-value="props.element.style.top || 0" @update:model-value="(e) => update('top', '' + e)"/>
            </ElFormItem>
            <ElFormItem label="Layer">
                <ElInputNumber :min='1' :max='999' :model-value="props.element.style.zIndex || 1" @update:model-value="(e) => update('zIndex', '' + e)"/>
            </ElFormItem>
            <ElFormItem label="Width" v-if="!props.element.hasRatio">
                <ElInputNumber :min='0' :max='maxWidth' :model-value="props.element.style.width || 0" @update:model-value="(e) => update('width', '' + e)"/>
            </ElFormItem>
            <span v-if="props.element.hasRatio">Element has a fixed ratio of {{  props.element.ratio }}. Only height can be adjusted.</span>
            <ElFormItem label="Height">
                <ElInputNumber :min='0' :max='maxHeight' :model-value="props.element.style.height || 0" @update:model-value="(e) => update('height', '' + e)"/>
            </ElFormItem>
            <ElFormItem label="FontSize" v-if="props.element.hasFontSize">
                <ElInputNumber :min='0' :max='300' :model-value="props.element.style.fontSize || 10" @update:model-value="(e) => update('fontSize', '' + e)"/>
            </ElFormItem>
            <ElFormItem label="Font" v-if="props.element.hasFontSize">
                <ElSelect :model-value="props.element.style.fontFamily || 'Helvetica'" @update:model-value="(e) => update('fontFamily', e)">
                    <ElOption v-for="font in fontvaluelist" :key="font.value" :value="font.name" :label="font.name"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem label="Alignment" v-if="props.element.hasFontSize">
                <ElSelect :model-value="props.element.style.textAlign || 'left'" @update:model-value="(e) => update('textAlign', e)">
                    <ElOption value="left" label="Left"/>
                    <ElOption value="center" label="Center"/>
                    <ElOption value="right" label="Right"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem label="Style" v-if="props.element.hasFontSize">
                <ElSelect :model-value="props.element.style.fontStyle || 'left'" @update:model-value="(e) => update('fontStyle', e)">
                    <ElOption value="regular" label="Regular"/>
                    <ElOption value="bold" label="Bold"/>
                    <ElOption value="italic" label="Italic"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem label="Text Colour" v-if="props.element.hasColour">
                <ElColorPicker :model-value="props.element.style.color || '#000000'" @update:model-value="(e) => update('colour', e)"/>
            </ElFormItem>
            <ElFormItem label="Background Colour" v-if="props.element.hasBackgroundColour">
                <ElColorPicker :model-value="props.element.style.backgroundColor || '#000000'" @update:model-value="(e) => update('backgroundColour', e)"/>
            </ElFormItem>
            <ElFormItem label="Content" v-if="element.type == 'text'">
                <ElInput :model-value="props.element.text" @update:model-value="(e) => update('text', e)"/>
            </ElFormItem>
            <ElFormItem label="Link" v-if="element.type == 'qr'">
                <ElInput :model-value="props.element.link" @update:model-value="(e) => update('link', e)"/>
            </ElFormItem>
            <ElFormItem label="Name" v-if="props.element.type == 'name'">
                <ElRadioGroup :model-value="props.element.name" @update:model-value="(e) => update('name', '' + e)">
                    <ElRadio label="wholename">Whole name</ElRadio>
                    <ElRadio label="firstname">Given name only</ElRadio>
                    <ElRadio label="lastname">Surname only</ElRadio>
                </ElRadioGroup>               
            </ElFormItem>
            <ElFormItem label="Side" v-if="props.element.type == 'accid'">
                <ElSelect :model-value="props.element.side || 'left'" @update:model-value="(e) => update('side', e)">
                    <ElOption value="both" label="Both"/>
                    <ElOption value="left" label="Left"/>
                    <ElOption value="right" label="Right"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem label="Earliest date" v-if="props.element.type == 'dates'">
                <ElCheckbox :model-value="props.element.onedateonly" @update:model-value="(e) => update('onedateonly', e ? 'Y' : 'N')" />
            </ElFormItem>
        </ElForm>
    </div>
</template>