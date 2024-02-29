<script lang="ts" setup>
import type { AccreditationStatistics } from "../../../common/api/schemas/accreditationstatistics";
import type { SideEvent } from "../../../common/api/schemas/sideevent";
import { parse_date } from "../../../common/functions";

const emits = defineEmits(['onSearch']);

const props = defineProps<{
    event:SideEvent;
    statLine?: AccreditationStatistics;
}>();

function onSearch()
{
    emits('onSearch', props.event);
}

import { ElIcon } from "element-plus";
import { Search } from '@element-plus/icons-vue';
</script>
<template>
    <div class="competition-line">
        <div class="title">{{  props.event.abbr }}</div>
        <div class="date">{{  parse_date(props.event.starts).format('ddd D') }}</div>
        <div class="number">{{ props.statLine?.registrations || 0 }}</div>
        <div class="number">{{ props.statLine?.pending || 0 }}</div>
        <div class="number">{{ props.statLine?.present || 0 }}</div>
        <div class="number">{{ props.statLine?.cancelled || 0 }}</div>
        <div class="number">{{ props.statLine?.checkin || 0 }}</div>
        <div class="number">{{ props.statLine?.checkout || 0 }}</div>
        <div class="search">
            <ElIcon size="large" @click="(e) => onSearch()">
                <Search />
            </ElIcon>
        </div>
    </div>
</template>