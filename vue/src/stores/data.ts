import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'
import { configuration as configurationAPI, basicSettings, forms as formsAPI, getSingleForm } from '../lib/api';
import type {Configuration, Sheet, Form, WPPost } from '../lib/types';
import lang from '@/lib/lang';

export const useDataStore = defineStore('data', () => {
    const nonce = ref('');
    const baseUrl = ref('');
    const configuration:Ref<Configuration> = ref({});
    const sheets:Ref<Array<Sheet>> = ref([]);
    const pages:Ref<Array<WPPost>> = ref([]);
    const forms:Ref<Array<Form>> = ref([]);
    const currentForm:Ref<Form>  =ref({id:0, name: '', sheet_id: 0, settings: {}});
    const attributes:Ref<Array<string>> = ref([]);

    function getConfiguration()
    {
        return configurationAPI().then(() => {
        });
    }

    function getBasicSettings()
    {
        return basicSettings(currentForm.value ? (currentForm.value.sheet_id || 0) : 0).then((data:any) => {
            attributes.value = data.data.attributes || [];
            sheets.value = data.data.sheets || [];
            pages.value = data.data.pages || [];
        });
    }

    function getForms()
    {
        return formsAPI().then((data) => {
            if (data && data.data) {
                forms.value = data.data.map((form:Form) => {
                    // workaround to fix json_encode that a converted empty PHP hash becomes an empty array
                    if ((typeof form.settings.length) != 'undefined' && form.settings.length == 0) {
                        form.settings = {};
                    }
                    return form;
                });
            }
        });
    }

    function getForm(id:number)
    {
        return getSingleForm(id)
            .then((data) => {
                if (data && data.data) {
                    currentForm.value = data.data;
                }
            })
            .catch((e) => {
                console.log(e);
                alert(lang.ERROR_NETWORK_GEN);
            });
    }

    return { 
        nonce, baseUrl, configuration, attributes, sheets, pages, forms, currentForm,
        getConfiguration, getBasicSettings, getForms, getForm,
    }
})
