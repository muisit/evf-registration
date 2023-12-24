<script lang="ts" setup>
import type { AccreditationDocument } from "../../../../common/api/schemas/accreditation";
import { summary } from "../../../../common/api/accreditations/summary";
import { download } from "../../../../common/api/accreditations/download";

const props = defineProps<{
    name: string;
    r: number;
    a: number;
    d: number;
    g: number;
    type:string;
    typeId:number;
    docs: AccreditationDocument[];
}>();
const emits = defineEmits(['onRefresh']);

function downloadDocument(doc:AccreditationDocument)
{
    if (doc.available == 'Y') {
        download(doc.id);
    }
}

function tooltipContent(doc:AccreditationDocument)
{
    if (doc.available == 'Y') {
        return 'Size ' + doc.size + '. Document is available for download. Please click to start the download';
    }
    return 'Document is pending, please wait for the document to complete.'
}

function noDocumentsFound()
{
    return props.docs.length == 0;
}

function createSummary()
{
    summary(props.type, props.typeId)
        .then(() => {
            emits('onRefresh');
        })
        .catch((e) => {
            console.log(e);
            alert("There was a network error while attempting to create these documents. Please reload the page and try again");
        });
}

import { Document, Loading, Refresh } from "@element-plus/icons-vue";
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
                <ElTooltip :content="tooltipContent(doc)" v-if="doc.available == 'Y'">
                    <ElIcon size="large" @click="downloadDocument(doc)">
                        <Document />
                    </ElIcon>
                    <span v-if="doc.size != '-'">{{ doc.size }}</span>
                </ElTooltip>
                <ElTooltip :content="tooltipContent(doc)" v-if="doc.available == 'N'">
                    <ElIcon size="large" class="is-loading">
                        <Loading />
                    </ElIcon>
                    <span v-if="doc.size != '-'">{{ doc.size }}</span>
                </ElTooltip>
            </div>
            <div v-if="noDocumentsFound()">
                <ElIcon size="large" @click="createSummary()">
                    <Refresh />
                </ElIcon>
            </div>
        </td>
    </tr>
</template>