import "bootstrap/dist/css/bootstrap.min.css";
import { createApp } from 'vue';
import Main from './Main.vue';
import axios from 'axios'
import VueAxios from 'vue-axios'
const app = createApp(Main)
app.use(VueAxios, axios)

app.provide('axios', app.config.globalProperties.axios)  // provide 'axios'
app.mount('#app')

import "bootstrap/dist/js/bootstrap.js";