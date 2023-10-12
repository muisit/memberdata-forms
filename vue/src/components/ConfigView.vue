<script lang="ts" setup>
import { watch } from 'vue';
import { saveconfiguration } from '@/lib/api';
import { useDataStore } from '@/stores/data';
const props = defineProps<{
    visible:boolean;
}>();
import lang from '@/lib/lang';

const data = useDataStore();

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            data.getConfiguration();
            data.getBasicSettings(data.configuration.sheet || 0);
        }
    },
    { immediate: true }
)

watch(
    () => data.configuration.sheet,
    (nw) => {
        if (props.visible) {
          data.getBasicSettings(nw || 0).then(() => {
          });
        }
    }
)


function saveConfig()
{
    return saveconfiguration(data.configuration).then((data:any) => {
        data.configuration = data.data;
    });
}
import { ElButton, ElInput, ElSelect, ElOption, ElCheckbox } from 'element-plus';
</script>
<template>
  <div>
    <p>
      {{ lang.DESCR1 }}<br/>
    </p>
    <div class="save-button">
      <ElButton type="primary" @click="saveConfig">{{  lang.SAVE }}</ElButton>
    </div>
  </div>
</template>
@/stores/data