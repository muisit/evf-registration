<script lang="ts" setup>
import { ref } from 'vue';
import { is_valid, random_hash, valid_date } from '../../../common/functions';
import type { Registration } from '../../../common/api/schemas/registration';
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import type { Code } from '../../../common/api/schemas/codes';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
import { savedocument } from '../../../common/api/accreditations/savedocument';
import { dayjs } from 'element-plus';

const props = defineProps<{
    visible:boolean;
    fencer:Fencer|null;
    badge: Code|null;
    document:AccreditationDocument|null;
}>();
const emits = defineEmits(['onClose', 'onSubmit']);
const auth = useAuthStore();
const basic = useBasicStore();
const reloadHash = ref(random_hash());

function closeForm()
{
    emits('onClose');
}

function endProcess()
{
    auth.isLoading('savedocument');
    savedocument({id: props.document?.id || 0, status: 'O'}).then((dt) => {
        auth.hasLoaded('savedocument');
        if (dt) {
            emits('onSubmit', dt);
        }
        else {
            throw new Error("invalid response");
        }
    })
    .catch((e) => {
        console.log(e);
        auth.hasLoaded('savedocument');
        alert("There was an error saving the state of this bag. Please reload the page and try again");
    });
}

function fencerIsHod()
{
    let retval = false;
    if (props.fencer && props.fencer.registrations) {
        props.fencer.registrations.map((r:Registration) => {
            if (!is_valid(r.sideEventId) && is_valid(r.roleId)) {
                // the Head-of-Delegation role is fixed to ID 2
                if (r.roleId == 2) {
                    // check that this person is in fact HoD of the country of the fencer
                    retval = props.document?.countryId == r.countryId;
                }
            }
        });
    }
    return retval;
}

function isAllowed()
{
    if (props.fencer && props.badge && props.document
       && props.document.processEnd && !props.document.checkout
       && (props.document.badge == props.badge.original
          || fencerIsHod()
          )
    ) {
        return true;
    }
    return false;
}

import { ElDialog, ElForm, ElFormItem, ElSwitch, ElButton } from 'element-plus';
import PhotoId from './special/PhotoId.vue';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Weapon Control Checkout" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <PhotoId v-if="props.fencer != null" :fencer="props.fencer" :reloadHash="reloadHash"/>
      <div v-if="props.fencer != null" :class="{
        checkoutdialog: true,
        checkoutAllowed: isAllowed()
        }">
        <div class="title field"><b>Recipient</b></div>
        <div class="field"><b>Name:</b> {{ props.fencer?.lastName }}, {{ props.fencer?.firstName }}</div>
        <div class="field"><b>Gender:</b> {{ props.fencer?.gender == 'F' ? 'Female' : 'Male' }}</div>
        <div class="field"><b>DOB:</b> {{ dayjs(props.fencer?.dateOfBirth).format('DD-MM-YYYY') }}</div>
        <div class="field">
            <b>Country:</b> {{ basic.countriesById['c' + props.fencer?.countryId]?.name }}
        </div>
      </div>
      <div :class="{
        checkoutdialog: true,
        checkoutAllowed: isAllowed()
        }">
        <div class="title field"><b>Owner</b></div>
        <div class="field"><b>Name:</b> {{ props.document?.name }}</div>
        <div class="field"><b>Country:</b> {{ basic.countriesById['c' + props.document?.countryId]?.name }}</div>
        <div class="field"><b>Dates:</b> {{ props.document?.dates?.join(', ') }}</div>
        <div class="field" v-if="props.document?.card"><b>Card:</b> {{ props.document?.card }}</div>
        <div class="field" v-if="props.document?.document"><b>Document:</b> {{ props.document?.document }}</div>
      </div>
      <div v-if="props.document?.status == 'E'" class="error">
        There were issues with the material during control. Please indicate this to the recipient.
      </div>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="closeForm">Cancel</ElButton>
          <ElButton type="primary" @click="endProcess">Checkout</ElButton>
        </span>
      </template>
    </ElDialog>
</template>