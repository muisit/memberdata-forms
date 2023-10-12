let controller:any = null;
import { useDataStore } from '../stores/data';
import lang from './lang';
import type { APIResult, Form } from './types';

export function abort_all_calls()
{
    if(controller) {
        controller.abort();
        controller = null;
    }
}

function validateResponse()
{
    return (res:Response) => {
        return res.json().then(json => {
            if (!json || !json.success) {
                if (json && (json.data && json.data.error)) {
                    throw new Error(lang.ERROR_VALIDATION);
                }
                else {
                    throw new Error(lang.ERROR_NETWORK_GEN);
                }
            }
            return json;
        })
    };
}

/*
function getFileNameFromContentDispostionHeader(header:string|null): string
{
    const contentDispostion = (header || '').split(';');
    const fileNameToken = `filename=`;

    let fileName = 'downloaded.dat';
    contentDispostion.forEach((thisValue) => {
        if (thisValue.trim().indexOf(fileNameToken) === 0) {
            fileName = decodeURIComponent(thisValue.replace(fileNameToken, ''));
        }
    });
    return fileName;
}
*/
/*
function attachmentResponse()
{
    return async (res:Response) => {
        const blob = await res.blob();
        const filename = getFileNameFromContentDispostionHeader(res.headers.get('content-disposition'));
        const mimetype = res.headers.get('content-type');

        const newBlob = new Blob([blob], {type: mimetype || 'text/plain'});
        // workaround for missing msSaveOrOpenBlob in type spec of Navigator
        if (window.navigator && (window.navigator as any).msSaveOrOpenBlob) {
            (window.navigator as any).msSaveOrOpenBlob(newBlob);
        }
        else {
            const objUrl = window.URL.createObjectURL(newBlob);

            const link = document.createElement('a');
            link.href = objUrl;
            link.download = filename;
            link.click();

            // For Firefox it is necessary to delay revoking the ObjectURL.
            setTimeout(() => { window.URL.revokeObjectURL(objUrl); }, 250);
        }
    };
}
*/

function validFetch(path:string, pdata:any, options:any, headers:any, responseHandler:Function): Promise<APIResult>
{
    if(!controller) {
        controller = new AbortController();
    }
    const contentHeaders = Object.assign({
        "Accept": "application/json",
        "Content-Type": "application/json"} , headers);

    const auth = useDataStore();

    const data:any = {
        path: path,
        nonce: auth ? auth.nonce : ''
    };
    if (pdata && Object.keys(pdata).length > 0) {
        data.model = pdata;
    } 

    const fetchOptions = Object.assign({}, {headers: contentHeaders}, options, {
        credentials: "same-origin",
        redirect: "manual",
        method: 'POST',
        signal: controller.signal,
        body: JSON.stringify(data)
    });

    return (fetch(auth ? auth.baseUrl : '', fetchOptions)
        .then(responseHandler())
        .catch(err => {
            if(err.name === "AbortError") {
                console.log('disregarding aborted call');
            }
            else {
                throw err;
            }
        })  as Promise<APIResult>);
}

function fetchJson(path:string, data={}, options = {}, headers = {}): Promise<APIResult>
{
    return validFetch(path, data, options, headers, validateResponse);
}

/*
function fetchAttachment(path:string, data = {}, options = {}, headers = {}): Promise<APIResult>
{
    headers = Object.assign({
        "Accept": "*",
    }, headers);
    return validFetch(path, data, options, headers, attachmentResponse);
}
*/

export function configuration(): Promise<APIResult>
{
    return fetchJson('configuration');
}

export function basicSettings(sheet:number): Promise<APIResult>
{
    return fetchJson('configuration/basic', {sheet: sheet});
}

export function saveconfiguration(data:any): Promise<APIResult>
{
    return fetchJson('configuration/save', data);
}

export function forms(): Promise<APIResult>
{
    return fetchJson('forms');
}

export function getSingleForm(id:number): Promise<APIResult>
{
    return fetchJson('forms/get', {id: id});
}

export function saveForm(data:Form|null): Promise<APIResult>
{
    if (data) {
        // convert a null to a 0 to avoid typing issues
        let newData:any = Object.assign({}, data);
        if (newData.sheet == 0) {
            newData.sheet = null;
        }
        return fetchJson('forms/save', newData);
    }
    return new Promise<APIResult>((r) => r({success:false}));
}

export function saveResults(id:number, results:string[]): Promise<APIResult>
{
    return fetchJson('forms/result', {id: id, results: results});
}