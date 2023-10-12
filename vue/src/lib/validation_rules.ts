import validator from 'validator';
import { dayjs } from 'element-plus';
import { pad, padFloat } from './functions';
import { convertDateToDayJSFormat, convertDateToDayJSDateFormat, convertDateToDayJSTimeFormat, replaceNowValue } from './functions';
import type { RuleSet } from '@/lib/types';
import lang from '@/lib/lang';

interface RuleValidationResult {
    value: string;
    message?: string;
}

interface FieldValues {
    rule: string;
    parameter: string;
    name: string;
    type: string;
    options: string;
}

export interface ValidationResult {
    messages: Array<string>;
    value: string;
}

export function validateField(rules:RuleSet, name: string, value:string, type:string, options:string): ValidationResult
{
    const messages:Array<string|null> = Object.keys(rules).map((rule:string) => {
        let ruleparameter = rules[rule];
        let fValue:FieldValues = {
            name: name,
            rule: rule,
            parameter: replaceNowValue(ruleparameter),
            type: type,
            options: replaceNowValue(options)
        }
    
        const result = validateRule(fValue, value);
        value = result.value;
        if (result.message) {
            return result.message;
        }
        return null;
    })
    .filter((msg:string|null) => msg !== null);
    return { messages: messages as string[], value: value};
}

function validateRule(fieldValues:FieldValues, value:string): RuleValidationResult
{
    if (fieldValues.rule == 'required' || value.length > 0) {
        if (ruleImplementations[fieldValues.rule]) {
            return ruleImplementations[fieldValues.rule](fieldValues, value);
        }
    }
    return {value: value};
}


function comparisonFunc(fieldValues:FieldValues, value:string, callback:Function): RuleValidationResult {
    if (fieldValues.type == 'int') {
        const limit = parseInt(fieldValues.parameter);
        const val = parseInt(value);
        return callback(fieldValues, 'value', val, limit);
    }
    else if (fieldValues.type == 'number') {
        const limitb = parseFloat(fieldValues.parameter);
        const valb = parseFloat(value);
        if (!isNaN(limitb) && !isNaN(valb)) {
            return callback(fieldValues, 'value', valb, limitb);
        }
    }
    else if(fieldValues.type == 'date') {
        // back-end format is always ISO
        const dt = dayjs(value);
        // assume parameter is formatted according to options format
        let dt2 = dayjs(fieldValues.parameter, convertDateToDayJSDateFormat(fieldValues.options));
        // allow parameter to be an ISO value as well
        if (!dt2.isValid()) {
            dt2 = dayjs(fieldValues.parameter);
        }
        if (dt.isValid() && dt2.isValid()) {
            return callback(fieldValues, 'date', dt, dt2);
        }
    }
    else if(fieldValues.type == 'time') {
        // format has no default
        const dta = dayjs(value, convertDateToDayJSTimeFormat(fieldValues.options));
        const dt2a = dayjs(fieldValues.parameter, convertDateToDayJSTimeFormat(fieldValues.options));
        if (dta.isValid() && dt2a.isValid()) {
            return callback(fieldValues, 'date', dta, dt2a);
        }
    }
    else {
        const limitc = parseInt(fieldValues.parameter);
        const valc = value.length;
        return callback(fieldValues, 'length', valc, limitc);
    }
    return {value: value};
}

interface RuleImplementationObject {
    [key:string]: Function
}
const ruleImplementations:RuleImplementationObject = {
    "required": function (fieldValues:FieldValues, value:string): RuleValidationResult {
        if (value.length == 0) {
            return {value: value, message: lang.replace(lang.MSGRULEREQUIRED, fieldValues.name)};
        }
        return {value: value};
    },
    'email': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        if (!validator.isEmail(value)) {
            return {value: value, message: lang.replace(lang.MSGRULEEMAIL, fieldValues.name)};
        }
        return {value: value};
    },
    'date': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        // value-format is fixed to ISO standard
        const dt = dayjs(value, 'YYYY-MM-DD');
        if (!dt.isValid()) {
            return {value: value, message: lang.replace(lang.MSGRULEDATE, fieldValues.name, fieldValues.options)};
        }
        // keep the back-end value in ISO format
        return {value: value};
    },
    'time': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        const dt = dayjs(value, convertDateToDayJSTimeFormat(fieldValues.options));
        if (!dt.isValid()) {
            return {value: value, message: lang.replace(lang.MSGRULETIME, fieldValues.name, fieldValues.options)};
        }
        return {value: value};
    },
    'lte': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        return comparisonFunc(fieldValues, value, (fieldValues:FieldValues, tp: string, val: number|any, limit: number|any) => {
            switch (tp) {
                case 'value':
                    if (val > limit) {
                        return {value: value, message: lang.replace(lang.MSGRULELTE, fieldValues.name, limit)};
                    }
                    break;
                case 'length':
                    if (val > limit) {
                        return {value: value, message: lang.replace(lang.MSGRULELTELENGTH, fieldValues.name, limit)};
                    }
                    break;
                case 'date':
                    if (val.isAfter(limit)) {
                        return {value: value, message: lang.replace(lang.MSGRULELTEDATE, fieldValues.name, limit.format(convertDateToDayJSFormat(fieldValues.options)))};
                    }
                    break;
            }
            return {value: value};
        });
    },
    'lt': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        return comparisonFunc(fieldValues, value, (fieldValues:FieldValues, tp: string, val: number|any, limit: number|any) => {
            switch (tp) {
                case 'value':
                    if (val >= limit) {
                        return {value: value, message: lang.replace(lang.MSGRULELT, fieldValues.name, limit)};
                    }
                    break;
                case 'length':
                    if (val >= limit) {
                        return {value: value, message: lang.replace(lang.MSGRULELTLENGTH, fieldValues.name, limit)};
                    }
                    break;
                case 'date':
                    if (!val.isBefore(limit)) {
                        return {value: value, message: lang.replace(lang.MSGRULELTDATE, fieldValues.name, limit.format(convertDateToDayJSFormat(fieldValues.options)))};
                    }
                    break;
            }
            return {value: value};
        });
    },
    'gt': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        return comparisonFunc(fieldValues, value, (fieldValues:FieldValues, tp: string, val: number|any, limit: number|any) => {
            switch (tp) {
                case 'value':
                    if (val <= limit) {
                        return {value: value, message: lang.replace(lang.MSGRULEGT, fieldValues.name, limit)};
                    }
                    break;
                case 'length':
                    if (val <= limit) {
                        return {value: value, message: lang.replace(lang.MSGRULEGTLENGTH, fieldValues.name, limit)};
                    }
                    break;
                case 'date':
                    if (!val.isAfter(limit)) {
                        return {value: value, message: lang.replace(lang.MSGRULEGTDATE, fieldValues.name, limit.format(convertDateToDayJSFormat(fieldValues.options)))};
                    }
                    break;
            }
            return {value: value};
        });
    },
    'gte': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        return comparisonFunc(fieldValues, value, (fieldValues:FieldValues, tp: string, val: number|any, limit: number|any) => {
            switch (tp) {
                case 'value':
                    if (val < limit) {
                        return {value: value, message: lang.replace(lang.MSGRULEGTE, fieldValues.name, limit)};
                    }
                    break;
                case 'length':
                    if (val < limit) {
                        return {value: value, message: lang.replace(lang.MSGRULEGTELENGTH, fieldValues.name, limit)};
                    }
                    break;
                case 'date':
                    if (val.isBefore(limit)) {
                        return {value: value, message: lang.replace(lang.MSGRULEGTEDATE, fieldValues.name, limit.format(convertDateToDayJSFormat(fieldValues.options)))};
                    }
                    break;
            }
            return {value: value};
        });
    },
    'int': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        let val = parseInt(value);
        if (isNaN(val)) {
            return {value: value, message: lang.replace(lang.MSGRULEINT, fieldValues.name)};
        }
        if (fieldValues.parameter.length) {
            let padSize = parseInt(fieldValues.parameter);
            if (!isNaN(padSize) && padSize > 1) {
                value = pad(val, padSize);
            }
        }
        else {
            value = '' + val;
        }
        return {value: value};
    },
    'number': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        let val = parseFloat(value);
        if (isNaN(val)) {
            return {value: value, message: lang.replace(lang.MSGRULENUMBER, fieldValues.name)};
        }
        if (fieldValues.parameter.length) {
            // the parameter uses a mask-type, like '0.00' to indicate two decimals
            let padSizes = fieldValues.parameter.split('.');
            let padSizePre = padSizes.length > 0 ? padSizes[0].length : 0;
            let padSizePost = padSizes.length > 1 ? padSizes[1].length : 0;
            value = padFloat(val, padSizePre, padSizePost);
        }
        else {
            value = '' + val;
        }
        return {value: value};
    },
    'trim': function (fieldValues:FieldValues, value:string): RuleValidationResult {
        return {value: value.trim()};
    }
}
