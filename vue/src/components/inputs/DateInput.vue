<script lang="ts" setup>
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
import { convertDateToDayJSDateFormat } from '@/lib/functions';
const props = defineProps<{
    field: Field;
    value: string;
}>();
defineEmits(['update']);

function getDateFormat()
{
    let format = 'Y-m-d';
    if (props.field && props.field.options) {
        format = props.field.options;
    }
    return convertDateToDayJSDateFormat(format);
}

import { ElDatePicker } from 'element-plus';
</script>
<template>
    <ElDatePicker
        :model-value="props.value"
        @update:model-value="(e) => $emit('update',e)"
        type="date"
        :placeholder="lang.PICKDATE"
        :format="getDateFormat()"
        value-format="YYYY-MM-DD"
    />
</template>