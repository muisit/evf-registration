<script lang="ts" setup>
import { ref } from 'vue';
import { random_hash } from '../../../common/functions';
import type { Fencer } from '../../../common/api/schemas/fencer';
import type { AccreditationDocument } from '../../../common/api/schemas/accreditationdocument';
import type { Code } from '../../../common/api/schemas/codes';
import { useAuthStore } from '../../../common/stores/auth';
import { useBasicStore } from '../../../common/stores/basic';
import { savedocument } from '../../../common/api/accreditations/savedocument';
import { fencerIsHod } from './lib/fencerIsHod';
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

function reprocess()
{
    auth.isLoading('savedocument');
    savedocument({id: props.document?.id || 0, status: 'P'}).then((dt) => {
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

function isAllowed()
{
    if (props.fencer && props.badge && props.document
       && props.document.processEnd && !props.document.checkout
       && (props.document.badge == props.badge.original
          || fencerIsHod(props.fencer, props.document?.countryId || 0)
          )
    ) {
        return true;
    }
    return false;
}

function isOwnBag()
{
    if (props.fencer && props.document) {
        return props.fencer.id == props.document.fencerId;
    }
    return false;
}

import { ElDialog, ElForm, ElFormItem, ElSwitch, ElButton } from 'element-plus';
import PhotoId from './special/PhotoId.vue';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Weapon Control Checkout" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <div :class="{
        checkoutdialog: true,
        checkoutAllowed: isAllowed()
        }">
        <PhotoId v-if="props.fencer != null" :fencer="props.fencer" :reloadHash="reloadHash"/>
        <div v-if="props.fencer != null" :class="{
          recipient: true,
          checkoutByHod: fencerIsHod(props.fencer, props.document?.countryId || 0) && !isOwnBag()
        }">
          <div class="title field"><b>Recipient</b></div>
          <div class="field"><b>Name:</b> {{ props.fencer?.lastName }}, {{ props.fencer?.firstName }}</div>
          <div class="field"><b>Gender:</b> {{ props.fencer?.gender == 'F' ? 'Female' : 'Male' }}</div>
          <div class="field"><b>DOB:</b> {{ dayjs(props.fencer?.dateOfBirth).format('DD-MM-YYYY') }}</div>
          <div class="field">
              <b>Country:</b> {{ basic.countriesById['c' + props.fencer?.countryId]?.name }}
          </div>
        </div>
        <div v-if="!isOwnBag()" class="owner">
          <div class="hodfield field">Note: Bag is checked out by the Head of Delegation!</div>
          <div class="title field"><b>Owner</b></div>
          <div class="field"><b>Name:</b> {{ props.document?.name }}</div>
          <div class="field"><b>Country:</b> {{ basic.countriesById['c' + props.document?.countryId]?.name }}</div>
        </div>
        <div class="details">
          <div class="title field"><b>Details</b></div>
          <div class="field"><b>Dates:</b> {{ props.document?.dates?.join(', ') }}</div>
          <div class="field" v-if="props.document?.document"><b>Document:</b> {{ props.document?.document }}</div>
          <div class="field" v-if="props.document?.card"><b>Card:</b><span class="card">{{ props.document?.card }}</span></div>
          <div class="field message" v-if="props.document?.status == 'C'"><b>Status:</b> Pending processing</div>
          <div class="field message" v-if="props.document?.status == 'P'"><b>Status:</b> Being processed</div>
          <div class="field message" v-if="props.document?.status == 'G'"><b>Status:</b> No issues, ready for checkout</div>
          <div class="field message" v-if="props.document?.status == 'E'"><b>Status:</b> ISSUES WITH MATERIAL, ready for checkout</div>
          <div class="field message" v-if="props.document?.status == 'O'"><b>Status:</b> Checked out</div>
        </div>
        <div v-if="props.document?.status == 'C' || props.document?.status == 'P'" class="error">
          This bag is still marked as being processed. Make sure you are checking out the right bag.
        </div>
        <div v-if="props.document?.status == 'E'" class="error">
          There were issues with the material during control. Please indicate this to the recipient.
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="closeForm">Cancel</ElButton>
          <ElButton @click="reprocess">Reprocess</ElButton>
          <ElButton type="primary" @click="endProcess">Checkout</ElButton>
        </span>
      </template>
    </ElDialog>
</template>