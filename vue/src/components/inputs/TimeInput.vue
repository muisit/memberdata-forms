<script lang="ts" setup>
import { ref } from 'vue';
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
import { convertDateToDayJSTimeFormat } from '@/lib/functions';
const props = defineProps<{
    field: Field;
    value: string;
}>();
const emits = defineEmits(['update']);

function getTimeFormat()
{
    let format = 'H:i:s';
    if (props.field && props.field.options) {
        format = props.field.options;
    }
    return convertDateToDayJSTimeFormat(format);
}

import { ElTimePicker } from 'element-plus';
</script>
<template>
    <ElTimePicker
        :model-value="props.value"
        @update:model-value="(e) => $emit('update',e)"
        :placeholder="lang.PICKTIME"
        :format="getTimeFormat()"
    />
</template>