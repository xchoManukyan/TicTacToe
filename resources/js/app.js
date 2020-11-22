require('./bootstrap');
import Vuetify from "vuetify";
import Vue from 'vue';
import VeeValidate from 'vee-validate';
import '@mdi/font/css/materialdesignicons.css'

window.Vue = require('vue');
Vue.config.devtools = true;
Vue.prototype.$lodash = _;
Vue.prototype.$csrf = csrf;

Vue.use(Vuetify);
Vue.use(VeeValidate);

const csrf = document.head.querySelector('meta[name="csrf-token"]').getAttribute('content');

import Game from "./views/Game";
import ProfileHeader from "./components/ProfileHeader"

Vue.component('profileHeader', ProfileHeader)
Vue.component('game', Game)


const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),

    created() {
    },

    mounted() {
    },
});
