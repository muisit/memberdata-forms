<script lang="ts" setup>
import type { Field } from '@/lib/types';
const props = defineProps<{
    field: Field;
    value: string;
}>();
defineEmits(['update']);

function getNumberPrecision()
{
    if (!props.field || !props.field.options) return 0;
    if (props.field.rules && (typeof props.field.rules['int']) != 'undefined') {
        return 0;
    }
    // expect 'before dot after'
    let vals = props.field.options.split('.');
    if (vals.length < 2) {
        // 'X.'' or 'X' or no setting
        return 0;
    }
    let precision = parseInt(vals[1]);
    if (isNaN(precision)) {
        return 0;
    }
    return precision;
}

function getMaximum()
{
    if (props.field && props.field.rules) {
        if ((typeof props.field.rules['lt'] != 'undefined')) {
            return parseFloat(props.field.rules['lt']);
        }
        if ((typeof props.field.rules['lte'] != 'undefined')) {
            return parseFloat(props.field.rules['lte']);
        }
    }
    return +Infinity;
}

function getMinimum()
{
    if (props.field && props.field.rules) {
        if ((typeof props.field.rules['gt'] != 'undefined')) {
            return parseFloat(props.field.rules['gt']);
        }
        if ((typeof props.field.rules['gte'] != 'undefined')) {
            return parseFloat(props.field.rules['gte']);
        }
    }
    return -Infinity;
}


import { ElInputNumber } from 'element-plus';
</script>
<template>
    <ElInputNumber
        :model-value="parseInt(props.value)"
        @update:model-value="(e) => $emit('update',e)"
        :precision="getNumberPrecision()"
        :max="getMaximum()"
        :min="getMinimum()"
    />
</template>