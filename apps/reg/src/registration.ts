import './assets/main.scss';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './Registration.vue';

const app = createApp(App);
app.use(createPinia());
console.log("pinia created");

app.mount('#app')
