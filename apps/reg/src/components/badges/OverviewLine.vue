<script lang="ts" setup>
import type { AccreditationDocument } from "../../../../common/api/schemas/accreditation";

const props = defineProps<{
    name: string;
    r: number;
    a: number;
    d: number;
    g: number;
    docs: AccreditationDocument[];
}>();

function downloadDocument(doc:AccreditationDocument)
{
    if (doc.available == 'Y') {

    }
}

function tooltipContent(doc:AccreditationDocument)
{
    if (doc.available == 'Y') {
        return 'Document is available for download. Please click to start the download';
    }
    return 'Document is pending, please wait for the document to complete.'
}

import { Document } from "@element-plus/icons-vue";
import { ElIcon, ElTooltip } from "element-plus";
</script>
<template>
    <tr>
        <td>{{ props.name }}</td>
        <td class="textright">{{  props.r }}</td>
        <td class="textright">{{  props.a }}</td>
        <td class="textright">{{  props.d }}</td>
        <td class="textright">{{  props.g }}</td>
        <td>
            <div :class="{
                'document-icon': true,
                'document-available': doc.available == 'Y',
                'document-pending': doc.available == 'N'
                }" v-for="doc in props.docs" :key="doc.id">
                <ElTooltip :content="tooltipContent(doc)">
                    <ElIcon size="large" @click="downloadDocument(doc)">
                        <Document />
                    </ElIcon>
                    <span v-if="doc.size != '-'">{{ doc.size }}</span>
                </ElTooltip>
            </div>
        </td>
    </tr>
</template>