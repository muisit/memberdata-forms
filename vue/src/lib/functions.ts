import { dayjs } from 'element-plus';

export function pad(nm:number, numdigits:number = 2):string {
    let retval = '' + nm;
    while (retval.length < numdigits) {
        retval = '0' + retval;
    }
    return retval;
}

export function padFloat(nm:number, numdigits:number = 1, numdecimals:number = 0):string {
    const values = ('' + nm).split('.');

    if (!isNaN(numdigits) && numdigits > 1 && values.length > 0) {
        values[0] = pad(parseInt(values[0]), numdigits);
    }
    if (!isNaN(numdecimals)) {
        if (numdecimals <= 0) {
            values[1] = '';
        }
        else {
            if (values[1].length > numdecimals) {
                values[1] = values[1].substring(0, numdecimals);
            }
            else {
                while(values[1].length < numdecimals) {
                    values[1] = values[1] + '0';
                }
            }
        }
    }
    return values[0] + (values[1] == '' ? '' : ('.' + values[1]));
}

export function is_valid(id:any) {
    if (!id) return false;
    if (id.id) return is_valid(id.id);
    if (parseInt(id) > 0) return true;
    return false;
}

export function random_between(min:number, max:number)
{
    return Math.floor((Math.random() * (max - min)) + 0.5) + min;
}

export function random_from_list(chars:string)
{
    return chars[random_between(0, chars.length)];
}

export function random_token()
{
    const randomChars="abcdefghijklmnopqrstuvwxyz0123456789";
    let retval = '';
    for (let i = 0; i< 16; i++) {
        retval += random_from_list(randomChars);
    }
    return retval;
}

export function convertDateToDayJSFormat(format:string)
{
    let retval = '';
    for (let i = 0; i < format.length; i++) {
        const c = format[i];
        switch (c) {
            case 'a': retval += 'a'; break;
            case 'A': retval += 'A'; break;
            case 'y': retval += 'YY'; break;
            case 'Y': retval += 'YYYY'; break;
            case 'n': retval += 'M'; break;
            case 'm': retval += 'MM'; break;
            case 'j': retval += 'D'; break;
            case 'd': retval += 'DD'; break;
            case 'H': retval += 'HH'; break;
            case 'h': retval += 'H'; break;
            case 'G': retval += 'hh'; break;
            case 'g': retval += 'h'; break;
            case 'i': retval += 'mm'; break;
            case 's': retval += 'ss'; break;
            case 'P': retval += 'Z'; break;
            default: retval += c; break;
        }
    }
    return retval;
}

export function convertDateToDayJSTimeFormat(format:string)
{
    let retval = '';
    for (let i = 0; i < format.length; i++) {
        const c = format[i];
        switch (c) {
            case 'a': retval += 'a'; break;
            case 'A': retval += 'A'; break;
            case 'H': retval += 'HH'; break;
            case 'h': retval += 'H'; break;
            case 'G': retval += 'hh'; break;
            case 'g': retval += 'h'; break;
            case 'i': retval += 'mm'; break;
            case 's': retval += 'ss'; break;
            case 'P': retval += 'Z'; break;
            default: retval += c; break;
        }
    }
    return retval;
}

export function convertDateToDayJSDateFormat(format:string)
{
    let retval = '';
    for (let i = 0; i < format.length; i++) {
        const c = format[i];
        switch (c) {
            case 'y': retval += 'YY'; break;
            case 'Y': retval += 'YYYY'; break;
            case 'n': retval += 'M'; break;
            case 'm': retval += 'MM'; break;
            case 'j': retval += 'D'; break;
            case 'd': retval += 'DD'; break;
            default: retval += c; break;
        }
    }
    return retval;
}

export function replaceNowValue(val:string)
{
    while (val.includes(':now:')) {
        val = val.replace(':now:', dayjs().format('YYYY-MM-DD HH:mm:ss'));
    }
    return val;
}
