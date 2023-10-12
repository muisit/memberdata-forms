<script lang="ts" setup>
import { ref } from 'vue';
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
const props = defineProps<{
    field: Field
}>();
const emits = defineEmits(['onUpdate']);

const showDialog = ref(false);

function showTextInput()
{
    return !props.field.type || ['text-line','number','date', 'email', 'time'].includes(props.field.type);
}

function showAreaInput()
{
    return props.field.type && ['text-area'].includes(props.field.type);
}

function showSelectInput()
{
    return props.field.type && ['select'].includes(props.field.type);
}

function showCheckboxInput()
{
    return props.field.type && ['checkbox'].includes(props.field.type);
}

function showMultiCheckboxInput()
{
    return props.field.type && ['mselect'].includes(props.field.type);
}

function showMultiRadioInput()
{
    return props.field.type && ['uselect'].includes(props.field.type);
}

import FieldDialog from './FieldDialog.vue';
import { Setting, Rank } from '@element-plus/icons-vue';
import { ElIcon, ElInput, ElSelect, ElOption, ElCheckbox, ElRadio, ElRadioButton } from 'element-plus'
</script>
<template>
    <div class="field-settings label-on-top">
        <label>{{  props.field.label }}</label>
        <div class="input">
            <ElInput v-if="showTextInput()"/>
            <ElInput v-if="showAreaInput()" type="textarea" :rows="3"/>
            <ElSelect v-if="showSelectInput()">
                <ElOption :label="lang.OPTION1" :value="1"/>
                <ElOption :label="lang.OPTION2" :value="2"/>
                <ElOption :label="lang.OPTION3" :value="3"/>
            </ElSelect>
            <span v-if="showCheckboxInput()">
                <ElCheckbox/> {{ props.field.description }}
            </span>
            <span v-if="showMultiCheckboxInput()">
                <div><ElCheckbox>{{  lang.OPTION1 }}</ElCheckbox></div>
                <div><ElCheckbox>{{  lang.OPTION2 }}</ElCheckbox></div>
                <div><ElCheckbox>{{  lang.OPTION3 }}</ElCheckbox></div>
            </span>
            <ElRadio v-if="showMultiRadioInput()">
                <ElRadio>{{ lang.OPTION1 }}</ElRadio>
                <ElRadio>{{ lang.OPTION2 }}</ElRadio>
                <ElRadio>{{ lang.OPTION3 }}</ElRadio>
            </ElRadio>
        </div>
        <div class="fieldvalue-handle">
            <ElIcon size="large">
                <Rank />
            </ElIcon>
        </div>
        <div class="settings">
            <ElIcon size="large" @click="() => showDialog = true"><Setting/></ElIcon>
        </div>
        <FieldDialog :visible="showDialog" @on-close="() => showDialog = false" @on-update="(e) => $emit('onUpdate', e)" :field="props.field" />
    </div>
</template>