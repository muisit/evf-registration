<script lang="ts" setup>
import { ref } from 'vue';
import { is_valid, random_hash, valid_date } from '../../../../common/functions';
import type { Fencer } from '../../../../common/api/schemas/fencer';
import { useDataStore } from '../../stores/data';
import { duplicateFencerCheck } from './lib/duplicateFencerCheck';
import { savefencer } from '../../../../common/api/fencers/savefencer';
import { uploadphoto } from '../../../../common/api/fencers/uploadphoto';

const props = defineProps<{
    visible:boolean;
    changeCountry:boolean;
    fencer:Fencer;
}>();
const emits = defineEmits(['onClose', 'onUpdate', 'onSave']);
const data = useDataStore();
const reloadHash = ref(random_hash());

function closeForm()
{
    emits('onClose');
}

function submitForm()
{
    duplicateFencerCheck(props.fencer)
        .then((result) => {
            if (result && result.id) {
                console.log('duplicates found');
                var country = data.countriesById['c' + result.countryId];
                var genderPronoun = result.gender == 'M' ? 'his' : 'her';

                if (props.changeCountry) {
                    alert('There already is a person with the exact name and date of birth ' +
                        'registered for ' + country.name + '.\r\nEither select that person if this is a ' +
                        'duplicate entry, or adjust the name data with initials or suffixes to ' +
                        'distinguish the persons.');
                }
                else if (result.countryId == props.fencer.countryId) {
                    alert('There already is a person with the exact name and date of birth ' +
                        'registered for this country.\r\nEither select that person if this is a ' +
                        'duplicate entry, or adjust the name data with initials or suffixes to ' +
                        'distinguish the persons.');
                }
                else {
                    alert('The database contains an entry of a person with the same name and date ' +
                        'of birth, but registered for ' + country.name + '.\r\nIf this person wants to ' +
                        'enter, please have the HoD of ' + country.name + ' enter ' + genderPronoun +
                        ' registration.\r\n\r\nIf the person wants to represent another country, please contact ' +
                        'the webmaster@veteransfencing.eu to have the person\'s country changed before ' +
                        'entering registrations.\r\n\r\nIf this really is a new person, please adjust the name ' +
                        'by adding initials or suffixes, or contact webmaster@veteransfencing.eu');
                }
            }
            else {
                console.log('no duplicates found. Saving data');
                saveFencerData()
                  .then(() => {
                      console.log('emitting onSave');
                      emits('onSave');
                      console.log('closing form');
                      closeForm();
                      console.log('end of FencerDialog submitForm');
                  });
            }
        });
}

function saveFencerData()
{
    return savefencer(props.fencer)
        .then((fencer:Fencer|null) => {
            if(fencer) {
                // update fields to account for back-office field validation changes
                update('lastName', fencer.lastName);
                update('firstName', fencer.firstName);
                update('gender', fencer.gender);
                update('dateOfBirth', fencer.dateOfBirth);
                update('id', fencer.id);
            }
            else {
                console.log('no fencer returned on save');
            }
        });
}

function saveFencerPhoto(fileObject:any)
{
    return uploadphoto(props.fencer, fileObject)
        .then((data) => {
            if (data && data.status == "ok") {
                update('photoStatus', 'Y');
            }
        });
}

function update(fieldName:string, value: any)
{
    emits('onUpdate', {field: fieldName, value: value});
}

function onSavePhoto(fileObject:any)
{
    if (!dataComplete()) {
        // should not occur, as dataComplete() is blocking for the input button
        alert("Please fill out the name and date fields before submitting a photo");
    }
    else {
        if (!is_valid(props.fencer.id)) {
            saveFencerData()
              .then(() => saveFencerPhoto(fileObject))
              .then(() => {
                  emits('onSave');
                  reloadHash.value = random_hash();
              });
        }
        else {
            saveFencerPhoto(fileObject)
              .then(() => {
                  emits('onSave');
                  reloadHash.value = random_hash();
              });
        }
    }
}

function dataComplete()
{
    if (!props.fencer.lastName || props.fencer.lastName.length < 2) {
        return false;
    }
    if (!props.fencer.firstName || props.fencer.firstName.length < 2) {
        return false;
    }
    if (!['F', 'M'].includes(props.fencer.gender || '')) {
        return false;
    }
    if (props.fencer.dateOfBirth && !valid_date(props.fencer.dateOfBirth)) {
        return false;
    }
    return true;
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElSelect, ElOption, ElButton, ElDatePicker } from 'element-plus';
import PhotoId from './PhotoId.vue';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Edit Fencer Information" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <ElForm>
        <ElFormItem label="Surname">
          <ElInput :model-value="props.fencer.lastName" @update:model-value="(e) => update('lastName', e.toUpperCase())"/>
        </ElFormItem>
        <ElFormItem label="First name">
          <ElInput :model-value="props.fencer.firstName" @update:model-value="(e) => update('firstName', e)"/>
        </ElFormItem>
        <ElFormItem label="Gender">
          <ElSelect :model-value="props.fencer.gender" @update:model-value="(e) => update('gender', e)">
            <ElOption value="M" label="Male" />
            <ElOption value="F" label="Female" />
          </ElSelect>
        </ElFormItem>
        <ElFormItem label="Date of birth">
          <ElDatePicker :model-value="props.fencer.dateOfBirth || ''" format="YYYY-MM-DD" value-format="YYYY-MM-DD"  @update:model-value="(e) => update('dateOfBirth', e)"/>
        </ElFormItem>
        <ElFormItem label="Country">
          <ElSelect :model-value="props.fencer.countryId || 0" @update:model-value="(e) => update('countryId', e)"  v-if="props.changeCountry">
            <ElOption v-for="country in data.countries" :key="country.id" :value="country.id" :label="country.name"/>
          </ElSelect>
          <label v-else>
            {{ data.countriesById['c' + props.fencer.countryId] ? data.countriesById['c' + props.fencer.countryId].name : 'Not set'}}
          </label>
        </ElFormItem>
        <ElFormItem label="Photo ID">
          <PhotoId @onSave="onSavePhoto" :fencer="props.fencer" :dataComplete="dataComplete()" @onStateChange="(e) => update('photoStatus', e)" :reloadHash="reloadHash"/>
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