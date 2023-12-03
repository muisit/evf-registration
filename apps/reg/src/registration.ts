import './assets/main.scss';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './Registration.vue';

let properties:any = {};
let search = window.location.search;
if (search.length > 0) {
    if (search[0] == '?') {
        search = search.substring(1);
    }
    let values = search.split(/&/);
    values.map((val:string) => {
        let kv = val.split(/=/); // only supports a single '=', but the rest should have been quoted anyway
        // match only on the allowed properties
        if (['event'].includes(kv[0])) {
            properties[kv[0]] = kv[1];
        }
    });
}

const app = createApp(App, properties);
app.use(createPinia());

app.mount('#app')
