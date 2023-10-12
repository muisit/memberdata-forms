<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Form, Field, APIResult } from '@/lib/types';
import { random_token } from '@/lib/functions';
import lang from '@/lib/lang';
const props = defineProps<{
    visible:boolean;
    form: Form;
}>();
const emits = defineEmits(['onUpdate']);

import { useDataStore } from '@/stores/data';
const data = useDataStore();

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            data.currentForm = Object.assign({}, props.form);
        }
    },
    { immediate: true }
)

watch(
    () => data.currentForm?.sheet,
    (nw) => {
        if (nw) {
            data.getBasicSettings();
        }
    },
    { immediate: true }
)

import { saveForm as saveFormAPI } from '@/lib/api';
function saveForm()
{
    saveFormAPI(data.currentForm)
        .then((retval:APIResult) => {
            if (retval && retval.data && retval.data.errors && retval.data.errors.length) {
                alert(lang.ERROR_SAVE_FORM + retval.data.errors.join('\r\n'));
            }
            else {
                emits('onUpdate', retval.data);
            }
        })
        .catch((e) => {
            console.log(e);
            alert(lang.ERROR_NETWORK);
        });
}

function addField()
{
    if (data.currentForm) {
        if (!data.currentForm.settings) {
            data.currentForm.settings = {};
        }
        if (!data.currentForm.settings.fields) {
            data.currentForm.settings.fields = [];
        }
        data.currentForm.settings.fields.push({label: lang.NEWFIELD, type: 'text-line', defaultValue: '', options: '', rules: {}});
    }
}

function getFields()
{
    if (data.currentForm) {
        if (!data.currentForm.settings || !data.currentForm.settings.fields) {
            return [];
        }
        return data.currentForm.settings.fields.map((fld:Field) => {
            fld.token = random_token();
            return fld;
        });
    }
    return [];
}

function onUpdate(settings:any)
{
    if (data.currentForm) {
        data.currentForm.settings.fields = data.currentForm.settings.fields.map((field:Field) => {
            if (field.token == settings.field.token) {
                switch(settings.fieldName) {
                    case 'label':
                        field.label = settings.value;
                        break;
                    case 'attribute':
                        field.attribute = settings.value;
                        break;
                    case 'rules':
                        field.rules = settings.value;
                        break;
                    case 'defaultValue':
                        field.defaultValue = settings.value;
                        break;
                    case 'options':
                        field.options = settings.value;
                        break;
                    case 'type':
                        field.type = settings.value;
                        break;
                }
            }
            return field;
        });
    }
}

function onSort(newlist:any)
{
    if (data.currentForm) {
        data.currentForm.settings.fields = newlist;
    }
}

function getSetting(field:string)
{
    if (data.currentForm && data.currentForm.settings) {
        if (data.currentForm.settings[field]) {
            return data.currentForm.settings[field];
        }
    }
    if (field == 'submitButton') {
        return lang.SUBMITBUTTONSUBMIT;
    }
    return '';
}

function setSetting(field:string, val:string)
{
    if (data.currentForm) {
        if (!data.currentForm.settings) {
            data.currentForm.settings = {};
        }
        data.currentForm.settings[field] = val;
    }
}

const datalistId = ref(random_token());

import { ElForm, ElFormItem, ElSelect, ElOption, ElInput, ElButton } from 'element-plus';
import FormFieldEditor from './FormFieldEditor.vue';
</script>
<template>
    <div class="form-pane">
        <datalist :id="datalistId">
            <option v-for="page in data.pages" :key="page.id" :value="page.slug">{{  page.title }}</option>
        </datalist>
        <ElForm>
            <ElFormItem>
                <label class="el-form-item__label">{{  lang.SHORTCODE }}</label>
                <span>[memberdata-forms name='{{  data.currentForm.name }}']</span>
            </ElFormItem>
            <ElFormItem :label="lang.NAME">
                <ElInput v-model="data.currentForm.name"/>
            </ElFormItem>
            <ElFormItem :label="lang.SHEET">
                <ElSelect v-model="data.currentForm.sheet">
                    <ElOption :value="0" :label="lang.SHEETSELECT"/>
                    <ElOption v-for="sheet in data.sheets" :key="sheet.id" :value="sheet.id" :label="sheet.name"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem :label="lang.SUBMITBUTTON">
                <ElSelect :model-value="getSetting('submitButton')" @update:model-value="(e) => setSetting('submitButton', e)">
                    <ElOption :value="lang.SUBMITBUTTONSUBMIT" :label="lang.SUBMITBUTTONSUBMIT"/>
                    <ElOption :value="lang.SUBMITBUTTONENTER" :label="lang.SUBMITBUTTONENTER"/>
                    <ElOption :value="lang.SUBMITBUTTONSEND" :label="lang.SUBMITBUTTONSEND"/>
                    <ElOption :value="lang.SUBMITBUTTONREGISTER" :label="lang.SUBMITBUTTONREGISTER"/>
                    <ElOption :value="lang.SUBMITBUTTONCLOSE" :label="lang.SUBMITBUTTONCLOSE"/>
                    <ElOption :value="lang.SUBMITBUTTONDONE" :label="lang.SUBMITBUTTONDONE"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem :label="lang.EMAILAFTER">
                <ElInput :model-value="getSetting('emailAfter')" @update:model-value="(e) => setSetting('emailAfter', e)"/>
                <div class="descr">{{ lang.EMAILAFTERDESCR }}</div>
            </ElFormItem>
            <ElFormItem :label="lang.EMAILSUBJECT">
                <ElInput :model-value="getSetting('emailSubject')" @update:model-value="(e) => setSetting('emailSubject', e)"/>
            </ElFormItem>
            <ElFormItem :label="lang.REDIRECTPAGE">
                <ElInput :model-value="getSetting('redirectPage')" @update:model-value="(e) => setSetting('redirectPage', e)" :list="datalistId"/>
            </ElFormItem>
            <div class="buttons">
                <ElButton @click="saveForm" type="primary">{{  lang.SAVE }}</ElButton>
            </div>

            <FormFieldEditor :fieldlist="getFields()" @add-field="addField" @on-update="onUpdate" @on-sort="onSort"/>
        </ElForm>
    </div>
</template>