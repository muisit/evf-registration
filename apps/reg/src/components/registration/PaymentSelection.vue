<script lang="ts" setup>
import { useDataStore } from '../../stores/data';
import { useAuthStore } from '../../../../common/stores/auth';
import { is_valid } from '../../../../common/functions';
import type { SelectOption } from '../../../../common/types';
import { isOpenForRegistration } from '../../../../common/lib/event';
const props = defineProps<{
    payments: string;
    isadmin: boolean;
}>();
const emits = defineEmits(['onUpdate']);

const auth = useAuthStore();
const data = useDataStore();

function paymentOptions():SelectOption[]
{
    let options:SelectOption[] = [];

    if (auth.isOrganisation() && !is_valid(data.currentCountry.id)) {
        options.push({label: 'By Organisation', value: 'O'});

        if (auth.isSysop()) {
            options.push({label: 'By EVF', value: 'E'});
        }
    }
    else if (data.currentEvent.payments == 'all') {
        options.push({label: 'As individual', value: 'I'});
        options.push({label: 'As group', value: 'G'});
    }
    return options;
}

function allowPaymentSelection()
{
    if (auth.isHod() && !isOpenForRegistration(data.currentEvent)) return false;
    return paymentOptions().length > 1 ? true : false;
}

import { ElFormItem, ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div class="payment-selection" v-if="allowPaymentSelection()">
        <ElFormItem label="Payment">
            <ElSelect :model-value="props.payments" @update:model-value="(e) => $emit('onUpdate', e)">
                <ElOption v-for="(po,i) in paymentOptions()" :key="i" :value="po.value" :label="po.label" />
            </ElSelect>
        </ElFormItem>
    </div>
</template>