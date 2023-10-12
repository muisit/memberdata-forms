<script lang="ts" setup>
import { ref } from 'vue';
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
import { replaceNowValue } from '@/lib/functions';
const props = defineProps<{
    field: Field;
    errors: Array<string>|null;
}>();
const emits = defineEmits(['update']);

const fieldValue = ref(replaceNowValue(props.field.defaultValue) || '');


function setFieldValue(v:string)
{
    fieldValue.value = v;
    emits('update', {field: props.field, value: v});
}

function fieldType()
{
    if (!props.field || !props.field.type) return 'text-line';
    if (['number', 'date', 'time', 'email'].includes(props.field.type) && (typeof props.field.rules['widget']) == 'undefined') {
        return 'text-line';
    }
    return props.field ? props.field.type : 'text-line';
}

function isRequired()
{
    return props.field && props.field.rules && (typeof props.field.rules['required'] != 'undefined');
}

import NumberInput from './inputs/NumberInput.vue';
import DateInput from './inputs/DateInput.vue';
import TimeInput from './inputs/DateInput.vue';
import SelectInput from './inputs/SelectInput.vue';
import { ElFormItem, ElInput, ElCheckbox } from 'element-plus';
</script>
<template>
    <div class="field-settings label-on-top" v-if="fieldType() != 'hidden'">
        <label v-if="isRequired()" :title="lang.ISREQUIRED">
            {{  props.field.label }}
            <span class='required-indicator'>*</span>
        </label>
        <label v-else>
            {{  props.field.label }}
        </label>
        <ElFormItem class="input">
            <ElInput v-if="fieldType() == 'text-line'" :model-value="fieldValue" @update:model-value="(e) => setFieldValue(e)"/>
            <ElInput v-if="fieldType() == 'text-area'" type='text-area' :model-value="fieldValue" @update:model-value="(e) => setFieldValue(e)"/>
            <ElInput v-if="fieldType() == 'email'" type='email' :model-value="fieldValue" @update:model-value="(e) => setFieldValue(e)"/>
            <NumberInput :field="props.field" v-if="fieldType() == 'number'" :value="fieldValue" @update="setFieldValue"/>
            <DateInput :field="props.field" v-if="fieldType() == 'date'" :value="fieldValue" @update="setFieldValue"/>
            <TimeInput :field="props.field" v-if="fieldType() == 'time'" :value="fieldValue" @update="setFieldValue"/>
            <SelectInput :field="props.field" v-if="['select', 'uselect', 'mselect'].includes(fieldType())" :value="fieldValue" @update="setFieldValue"/>
            <ElCheckbox v-if="fieldType() == 'checkbox'" :model-value="fieldValue == 'Y'" @update:model-value="(e) => setFieldValue(e ? 'Y' : 'N')" />
        </ElFormItem>
        <div class="errors">
            <span v-for="error in props.errors" :key="error">{{  error }}<br/></span>
        </div>
    </div>
</template>