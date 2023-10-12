<script lang="ts" setup>
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
const props = defineProps<{
    fieldlist: Array<Field>
}>();
const emits = defineEmits(['addField', 'onUpdate', 'onSort']);

function dragStart()
{

}

import FieldSettings from './FieldSettings.vue';
import draggable from 'vuedraggable';
import { ElButton } from 'element-plus';
</script>
<template>
    <div class="form-field-editor">
        <hr>
        <div class="action-buttons">
            <ElButton @click="() => $emit('addField')" type="primary">{{ lang.ADD }}</ElButton>
        </div>
        <div class="form-fields">
            <draggable
                :model-value="props.fieldlist" 
                @update:model-value="(e) => $emit('onSort', e)"
                handle=".fieldvalue-handle"
                @start="dragStart" 
                item-key="item.token">
                <template #item="{element}">
                    <FieldSettings :field="element" @on-update="(e) => $emit('onUpdate', e)"/>
                </template>
            </draggable>
        </div>
    </div>
</template>