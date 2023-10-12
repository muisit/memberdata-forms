<script lang="ts" setup>
import { ref } from 'vue';
import type { Ref } from 'vue';
const props = defineProps<{
    nonce:string;
    url:string;
    form:number;
}>();
import type { Field } from './lib/types';
import { random_token } from './lib/functions';
import { useDataStore } from './stores/data';
import lang from '@/lib/lang';
import { validate_all_fields } from '@/lib/validate_all_fields';
import { saveResults } from '@/lib/api';
const data = useDataStore();
data.nonce = props.nonce;
data.baseUrl = props.url;
data.getForm(props.form);

const fieldValues:Ref<Array<string>> = ref([]);
const fieldErrors:Ref<Array<Array<string>>> = ref([]);

function getFields()
{
    if (!data.currentForm || !data.currentForm.settings || !data.currentForm.settings.fields) {
        return [];
    }
    return data.currentForm.settings.fields.map((fld:Field) => {
        fld.token = random_token();
        return fld;
    });
}

function getSubmitLabel()
{
    if(!data.currentForm || !data.currentForm.settings || !data.currentForm.settings.submitButton) {
        return lang.SUBMITBUTTONSUBMIT;
    }
    return data.currentForm.settings.submitButton;
}

function validateAndSubmit()
{
    let validationResult = validate_all_fields(getFields(), fieldValues.value);
    fieldErrors.value = [];
    let results:string[] = [];
    var cnt = 0;
    validationResult.forEach((result) => {
        fieldErrors.value.push(result.messages);
        cnt += result.messages.length;
        results.push(result.value);
    });

    if (cnt == 0) {
        // save, then redirect to the thank you page
        console.log(results);
        saveResults(data.currentForm.id, results)
            .then((data) => {
                if (!data || !data.data) {
                    throw new Error("Invalid data");
                }
                if (data.data.messages) {
                    fieldErrors.value = data.data.messages;
                }
                else if(data.data.url) {
                    window.location = data.data.url;
                }
                else {
                    alert(lang.RESULTS_SAVED);
                }
            })
            .catch((e) => {
                console.log(e);
                alert(lang.ERROR_SAVE_RESULTS);
            });
    }
}

function getErrorsForField(index:number)
{
    if (fieldErrors.value.length > index) {
        if (fieldErrors.value[index].length > 0) {
            return fieldErrors.value[index];
        }
    }
    return null;
}

function setValue(index:number, vals:any)
{
    while (fieldValues.value.length <= index) {
        fieldValues.value.push('');
    }
    fieldValues.value[index] = vals.value;
    if (fieldErrors.value.length > index) {
        fieldErrors.value[index] = [];
    }
}

import { ElForm, ElButton } from 'element-plus';
import FEField from './components/FEField.vue';
</script>
<template>
    <div class="memberdata-forms">
        <ElForm>
          <FEField v-for="(field, index) in getFields()" :key="index" :field="field" :errors="getErrorsForField(index)" @update="(e) => setValue(index, e)"/>
          <div class="action-buttons">
              <ElButton type="primary" @click="validateAndSubmit">{{ getSubmitLabel()}}</ElButton>
          </div>
        </ElForm>
    </div>
</template>
