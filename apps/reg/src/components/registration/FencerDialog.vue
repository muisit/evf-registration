<script lang="ts" setup>
import { ref } from 'vue';
import { Fencer } from '../../../../common/api/schemas/fencer';
import { useAuthStore } from '../../../../common/stores/auth';
import { useDataStore } from '../../stores/data';
import { duplicateFencerCheck } from './lib/duplicateFencerCheck';

const props = defineProps<{
    visible:boolean;
    fencer:Fencer;
}>();
const emits = defineEmits(['onClose', 'onSave', 'onChange']);
const auth = useAuthStore();
const data = useDataStore();

function closeForm()
{
    emits('onClose');
}

function submitForm()
{
    console.log('fencer dialog, submit form, calling duplicate');
    duplicateFencerCheck(props.fencer)
        .then((result) => {
            console.log('duplicate returns ', result);
            if (result && result.id) {
                if (result.countryId == props.fencer.countryId) {
                    alert('There already is a fencer with the exact name and date of birth ' +
                        'registered for this country.\r\nEither select that fencer if this is a ' +
                        'duplicate entry, or adjust the name data with initials or suffixes to ' +
                        'distinguish the fencers.');
                }
                else {
                    var country = data.countriesById['c' + result.countryId];
                    var genderPronoun = result.gender == 'M' ? 'his' : 'her';
                    alert('The database contains an entry of a fencer with the same name and date ' +
                        'of birth, but registered for ' + country.name + '.\r\nIf this fencer wants to ' +
                        'enter, please have the HoD of ' + country.name + ' enter ' + genderPronoun +
                        ' registration.\r\n\r\nIf the fencer wants to represent another country, please contact ' +
                        'the webmaster@veteransfencing.eu to have the person\'s country changed before ' +
                        'entering registrations.\r\n\r\nIf this really is a new fencer, please adjust the name ' +
                        'by adding initials or suffixes, or contact webmaster@veteransfencing.eu');
                }
            }
            else {
                emits('onSave');
                closeForm();
            }
        });
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElButton, ElDatePicker } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Edit Fencer Information" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <ElForm>
        <ElFormItem label="Last name">
          <ElInput v-model="props.fencer.lastName"/>
        </ElFormItem>
        <ElFormItem label="First name">
          <ElInput v-model="props.fencer.firstName"/>
        </ElFormItem>
        <ElFormItem label="Gender">
          <ElSelect v-model="props.fencer.gender">
            <ElOption value="M" label="Man" />
            <ElOption value="F" label="Woman" />
          </ElSelect>
        </ElFormItem>
        <ElFormItem label="Date of birth">
          <ElDatePicker v-model="props.fencer.dateOfBirth" format="YYYY-MM-DD" value-format="YYYY-MM-DD"/>
        </ElFormItem>
        <ElFormItem label="Country">
          <ElSelect v-model="props.fencer.countryId">
            <ElOption v-for="country in data.countries" :key="country.id" :value="country.id" :label="country.name"/>
          </ElSelect>
        </ElFormItem>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="closeForm">Cancel</ElButton>
          <ElButton type="primary" @click="submitForm">Save</ElButton>
        </span>
      </template>
    </ElDialog>
</template>