<script lang="ts" setup>
import { ref } from 'vue';
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
const props = defineProps<{
    field: Field;
    value: string;
}>();
const emits = defineEmits(['update']);

function hasSelect()
{
    if(props.field.type == 'select') return true;
    if (props.field.type == 'uselect') {
        let opts = getOptions();
        if (opts.length > 10) {
            return true;
        }
    }
    return false;
}

function hasCheckboxes()
{
    return props.field.type == 'mselect';
}

function hasRadios()
{
    if (props.field.type == 'uselect') {
        let opts = getOptions();
        if (opts.length <= 10) {
            return true;
        }
    }
    return false;
}

function getOptions()
{
    if (!props.field.options) return [];
    let opts = props.field.options.split('|');
    return opts;
}

function isSet(opt)
{
    
    return vals.includes(opt);
}

function toggleOpt(opt, isset)
{
    let vals = props.value.split('|');
    if (isset && vals.includes(opt)) {
        vals = vals.filter((v) => v != opt);
    }
    else if(!isset && !vals.includes(opt)) {
        vals.push(opt);
    }
    emits('update', vals.join('|'));
}

import { ElSelect, ElOption, ElRadio, ElRadioGroup, ElCheckbox } from 'element-plus';
</script>
<template>
    <div class="select-input">
        <ElSelect v-if="hasSelect()" :model-value="props.value" @update:model-value="(e) => $emit('update',e)">
            <ElOption v-for="opt in getOptions()" :value="opt" :key="opt" :label="opt"/>
        </ElSelect>
        <ElRadioGroup v-if="hasRadios()" :model-value="props.value" @update:model-value="(e) => $emit('update',e)">
            <ElRadio v-for="opt in getOptions()" :key="opt" :label="opt">{{  opt }}</ElRadio>
        </ElRadioGroup>
        <div class="check-group" v-if="hasCheckboxes()" >
            <ElCheckbox v-for="opt in getOptions()" :key="opt" :model-value="isSet(opt)" @update:model-value="(e) => toggleOpt(opt, e)">{{ opt }}</ElCheckbox>
        </div>
    </div>
</template>