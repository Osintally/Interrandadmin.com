/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import { createApp } from 'vue'

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = createApp({})

app.component('example', require('./components/Example.vue').default);
app.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);
app.component(
    'passport-authorized-clients', 
    require('./components/passport/AuthorizedClients.vue').default
);
app.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

app.mount('#app')