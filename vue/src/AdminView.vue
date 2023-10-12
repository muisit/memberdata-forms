<script lang="ts" setup>
import { ref } from 'vue';
import { useDataStore } from './stores/data';
import lang from '@/lib/lang';
import type { Form, APIResult } from '@/lib/types';
import { random_token } from '@/lib/functions';
const props = defineProps<{
    nonce:string;
    url:string;
}>();

const data = useDataStore();
data.nonce = props.nonce;
data.baseUrl = props.url;
data.getConfiguration();
data.getForms().then(() => tabindex.value = '' + data.forms[0].id);
data.getBasicSettings();

const formDialog = ref(false);
const tabindex = ref('settings');
const tabskey = ref(random_token());

import { saveForm  } from '@/lib/api';
function addForm()
{
    saveForm({name: lang.NEWFORM, sheet_id: null, settings: null})
        .then((retval:APIResult) => {
            if (retval && retval.data && retval.data.errors && retval.data.errors.length) {
                alert(lang.ERROR_SAVE_FORM + retval.data.errors.join('\r\n'));
            }
            else {
                tabindex.value = retval.data.name;
                return data.getForms();
            }
        })
        .catch((e) => {
            console.log(e);
            alert(lang.ERROR_NETWORK);
        });
}

function onUpdateForm(newForm:Form)
{
    tabindex.value = '' + newForm.id;
    data.getForms().then(() => tabskey.value = random_token());
}

import { ElTabs, ElTabPane, ElButton } from 'element-plus';
//import ConfigView from './components/ConfigView.vue';
import FormTab from './components/FormTab.vue';
</script>
<template>
    <div>
        <h1>{{ lang.ADMIN_PAGE }}</h1>
        <div class="action-buttons">
            <ElButton @click="addForm" type="primary">{{ lang.ADDFORM }}</ElButton>
        </div>
        <ElTabs v-model="tabindex" :key="tabskey">
            <ElTabPane v-for="form in data.forms" :key="form.id" :label="form.name" :name="'' + form.id">
                <FormTab :form="form" :visible="tabindex == ('' + form.id)" @on-update="onUpdateForm"/>
            </ElTabPane>
    </ElTabs>
  </div>
</template>
./stores/data