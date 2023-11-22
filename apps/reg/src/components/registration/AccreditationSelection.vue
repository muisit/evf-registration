<script lang="ts" setup>
import { ref, Ref, watch } from 'vue'
import { Accreditation, AccreditationList } from '../../../../common/api/schemas/accreditation';
import { fetchAttachment } from '../../../../common/api/interface';
import { Fencer } from '../../../../common/api/schemas/fencer';
import { accreditations } from '../../../../common/api/fencers/accreditations';
import { useDataStore } from '../../stores/data';
import { is_valid } from '../../../../common/functions';
const props = defineProps<{
    fencer: Fencer;
}>();

const data = useDataStore();
const accreditationList:Ref<AccreditationList> = ref([]);
watch(
    () => props.fencer.id,
    (nw) => {
        accreditationList.value = [];
        if (is_valid(nw)) {
            accreditations(nw).then((data) => {
                accreditationList.value = data.filter((accr:Accreditation) => accr.hasFile == 'Y');
            });
        }
    },
    { immediate: true }
)

function download(accreditation:Accreditation)
{
    fetchAttachment('/accreditations/' + accreditation.id + '/badge');
}

import { Download } from '@element-plus/icons-vue';
import { ElIcon, ElButton } from 'element-plus';
</script>
<template>
    <div class="accreditation-selection" v-if="accreditationList.length > 0">
        <h3>Accreditation Badges</h3>
        <table class="fencer-select-accreditations">
            <tbody>
                <tr v-for="accr in accreditationList" :key="accr.id">
                    <td>
                        {{  accr.template }}
                    </td>
                    <td>
                        <ElButton @click="download(accr)">
                            Download
                            <ElIcon size="large" >
                                <Download />
                            </ElIcon>
                        </ElButton>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
