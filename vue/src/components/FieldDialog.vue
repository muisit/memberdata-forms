<script lang="ts" setup>
import type { Field } from '@/lib/types';
import lang from '@/lib/lang';
const props = defineProps<{
    visible: boolean;
    field:Field;
}>();
const emits = defineEmits(['onClose', 'onSave', 'onUpdate']);

import { useDataStore } from '@/stores/data';
const data = useDataStore();

function closeDialog()
{
    emits('onClose');
}

function update(fieldName:string, value:string|object) {
    emits('onUpdate', {field: props.field, fieldName: fieldName, value:value})
}

function hasOptions()
{
    return props.field.type && ['select', 'mselect', 'uselect'].includes(props.field.type);
}
function hasFormat()
{
    return props.field.type && ['number', 'date', 'time'].includes(props.field.type);
}
function hasWidget()
{
    return props.field.type && ['number', 'email', 'date', 'time'].includes(props.field.type);
}

function ruleSet(rulename:string)
{
    return props.field.rules && (typeof props.field.rules[rulename]) != 'undefined';
}

function ruleOption(rulename:string)
{
    return props.field.rules ? props.field.rules[rulename] : '';
}

function updateRuleOption(rulename:string, val:string)
{
    let rules = Object.assign({}, props.field.rules || {});
    rules[rulename] = val;
    update('rules', rules);
}

function updateRule(rulename:string, isset:any)
{
    let rules = Object.assign({}, props.field.rules || {});
    if ((typeof rules[rulename]) != 'undefined' && !isset) {
        delete rules[rulename];
    }
    else if (!rules[rulename] && isset) {
        rules[rulename] = '';
    }
    update('rules', rules);
}

function hasValueType()
{
    return props.field.type && ['number', 'date', 'time'].includes(props.field.type);
}

function hasTextType()
{
    return props.field.type && ['text-line', 'text-area'].includes(props.field.type);
}

function hasNumericType()
{
    return props.field.type && ['number'].includes(props.field.type);
}

function isNotHiddenType()
{
    return !props.field.type || props.field.type != 'hidden';
}

import { ElForm, ElFormItem, ElInput, ElButton, ElDialog, ElSelect, ElOption, ElCheckbox } from 'element-plus'
</script>
<template>
    <ElDialog :model-value="props.visible" :title="lang.FIELDDIALOG_TITLE" :before-close="(done) => { closeDialog(); done(false); }">
        <ElForm>
            <ElFormItem :label="lang.LABEL">
                <ElInput :model-value="props.field.label || ''" @update:model-value="(e) => update('label', e)"/>
            </ElFormItem>
            <ElFormItem :label="lang.TYPE">
                <ElSelect  :model-value="props.field.type || 'text'" @update:model-value="(e) => update('type', e)">
                    <ElOption value="text-line" :label="lang.OPTIONTEXT"/>
                    <ElOption value="text-area" :label="lang.OPTIONAREA"/>
                    <ElOption value="number" :label="lang.OPTIONNUMBER"/>
                    <ElOption value="date" :label="lang.OPTIONDATE"/>
                    <ElOption value="time" :label="lang.OPTIONTIME"/>
                    <ElOption value="email" :label="lang.OPTIONEMAIL"/>
                    <ElOption value="select" :label="lang.OPTIONSELECT"/>
                    <ElOption value="mselect" :label="lang.OPTIONMSELECT"/>
                    <ElOption value="uselect" :label="lang.OPTIONUSELECT"/>
                    <ElOption value="checkbox" :label="lang.OPTIONCHECKBOX"/>
                    <ElOption value="hidden" :label="lang.OPTIONHIDDEN"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem :label="lang.ATTRIBUTE">
                <ElSelect  :model-value="props.field.attribute || ''" @update:model-value="(e) => update('attribute', e)">
                    <ElOption :label="lang.SELECTATTRIBUTE" value=""/>
                    <ElOption v-for="attr in data.attributes" :key="attr" :value="attr" :label="attr"/>
                </ElSelect>
            </ElFormItem>
            <ElFormItem :label="lang.DEFAULTVALUE">
                <ElInput :model-value="props.field.defaultValue || ''" @update:model-value="(e) => update('defaultValue', e)"/>
                <div class="descr">{{ lang.DEFAULTVALUEDESCR }}</div>
            </ElFormItem>
            <ElFormItem :label="lang.OPTIONS" v-if="hasOptions()">
                <ElInput :model-value="props.field.options || ''" @update:model-value="(e) => update('options', e)" type="area"/>
                <div class="descr">{{ lang.OPTIONSDESCR }}</div>
            </ElFormItem>
            <ElFormItem :label="lang.FORMAT" v-if="hasFormat()">
                <ElInput :model-value="props.field.options || ''" @update:model-value="(e) => update('options', e)" type="area"/>
                <div class="descr">{{ lang.FORMATDESCR }}</div>
            </ElFormItem>
            <ElFormItem v-if="hasWidget()">
                <label class="el-form-item__label">{{ lang.RULEWIDGET }}</label>
                <ElCheckbox
                    :model-value="ruleSet('widget')"
                    @update:model-value="(e) => updateRule('widget', e)"
                    label='widget'>
                    {{ lang.RULEUSEWIDGET }}
                </ElCheckbox>               
            </ElFormItem>
            <ElFormItem v-if="isNotHiddenType()">
                <label class="el-form-item__label">{{ lang.RULES }}</label>
                <table>
                    <tr>
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('required')"
                                @update:model-value="(e) => updateRule('required', e)"
                                label='required'>
                                {{ lang.RULEREQUIRED }}
                            </ElCheckbox>
                        </td>
                        <td></td>
                    </tr>
                    <tr v-if="hasValueType()">
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('lt')"
                                @update:model-value="(e) => updateRule('lt', e)"
                                label='lt'>
                                {{ lang.RULELT }}
                            </ElCheckbox>
                        </td>
                        <td>
                            <ElInput
                                :model-value="ruleOption('lt')"
                                @update:model-value="(e) => updateRuleOption('lt', e)"
                            />
                        </td>
                    </tr>
                    <tr v-if="hasValueType()">
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('lte')"
                                @update:model-value="(e) => updateRule('lte', e)"
                                label='lte'>
                                {{ lang.RULELTE }}
                            </ElCheckbox>
                        </td>
                        <td>
                            <ElInput
                                :model-value="ruleOption('lte')"
                                @update:model-value="(e) => updateRuleOption('lte', e)"
                            />
                        </td>
                    </tr>
                    <tr v-if="hasValueType()">
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('gte')"
                                @update:model-value="(e) => updateRule('gte', e)"
                                label='gte'>
                                {{ lang.RULEGTE }}
                            </ElCheckbox>
                        </td>
                        <td>
                            <ElInput
                                :model-value="ruleOption('gte')"
                                @update:model-value="(e) => updateRuleOption('gte', e)"
                            />
                        </td>
                    </tr>
                    <tr v-if="hasValueType()">
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('gt')"
                                @update:model-value="(e) => updateRule('gt', e)"
                                label='gt'>
                                {{ lang.RULEGT }}
                            </ElCheckbox>
                        </td>
                        <td>
                            <ElInput
                                :model-value="ruleOption('gt')"
                                @update:model-value="(e) => updateRuleOption('gt', e)"
                            />
                        </td>
                    </tr>
                    <tr v-if="hasTextType()">
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('trim')"
                                @update:model-value="(e) => updateRule('trim', e)"
                                label='trim'>
                                {{ lang.RULETRIM }}
                            </ElCheckbox>
                        </td>
                        <td></td>
                    </tr>
                    <tr v-if="hasNumericType()">
                        <td>
                            <ElCheckbox
                                :model-value="ruleSet('int')"
                                @update:model-value="(e) => updateRule('int', e)"
                                label='int'>
                                {{ lang.RULEINT }}
                            </ElCheckbox>
                        </td>
                        <td></td>
                    </tr>
                </table>
            </ElFormItem>
        </ElForm>
        <template #footer>
            <span class="dialog-footer">
                <ElButton type="warning" @click="closeDialog">{{ lang.CLOSE }}</ElButton>
            </span>
        </template>
    </ElDialog>    
</template>