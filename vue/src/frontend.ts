import 'element-plus/dist/index.css'
import './assets/fe.scss'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './FrontendView.vue'

const el = document.getElementById('memberdata_forms-fe');
let props = {};
if (el) {
    const data = el.getAttribute('data-memberdata_forms');
    if (data) {
        props = JSON.parse(data);
    }
}

const app = createApp(App, props);
app.use(createPinia())
app.mount('#memberdata_forms-fe');
