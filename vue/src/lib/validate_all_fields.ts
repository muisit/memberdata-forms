import type { Field } from '@/lib/types';
import { validateField } from './validation_rules';
import type { ValidationResult } from './validation_rules';
import { replaceNowValue } from './functions';

export function validate_all_fields(fields:Field[], values:string[]): Array<ValidationResult>
{
    const retval:Array<ValidationResult> = [];
    fields.forEach((v:Field, index:number) => {
        let val = values.length > index ? values[index] : '';

        // depending on the type of the field, add additional rules using the options
        switch (v.type) {
            case "text-line":
            case "text-area":
                v.rules['trim'] = '';
                break;
            case "number":
                if ((typeof v.rules['number']) == 'undefined') {
                    v.rules['number'] = v.options;
                }
                break;
            case "date":
                if ((typeof v.rules['time']) == 'undefined') {
                    v.rules['date'] = v.options;
                }
                break;
            case "time":
                if ((typeof v.rules['time']) == 'undefined') {
                    v.rules['time'] = v.options;
                }
                break;
            case "email":
                if ((typeof v.rules['email']) == 'undefined') {
                    v.rules['email'] = '';
                }
                break;
            case "hidden":
                val = replaceNowValue(v.defaultValue);
                break;
            case "select":
            case "mselect":
            case "uselect":
            case "checkbox":
            default:
                break;
        }

        retval.push(validateField(v.rules, v.label, val, v.type, v.options));
    });

    return retval;
}
