import './assets/main.scss';

//import { library } from '@fortawesome/fontawesome-svg-core'
//import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './Accredit.vue';

let properties:any = {event: 0};
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
            properties[kv[0]] = parseInt(kv[1]);
        }
    });
}
console.log('creating app using properties', properties);
const app = createApp(App, properties);
app.use(createPinia());
//app.component('font-awesome-icon', FontAwesomeIcon);
app.mount('#app')
