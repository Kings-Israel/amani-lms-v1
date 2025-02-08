import Vue from "vue";

import Vuelidate from 'vuelidate'
Vue.use(Vuelidate)

import store from "./store.js"

import vSelect from 'vue-select'
Vue.component('v-select', vSelect)

import Datepicker from 'vuejs-datepicker'
Vue.component('date-picker', Datepicker)

import RegisterComponent from './components/customer/RegisterComponent'
import EditComponent from './components/customer/EditComponent'
Vue.component('register-component', RegisterComponent)
Vue.component('edit-component', EditComponent)

Vue.config.productionTip = false;


/* eslint-disable no-new */
new Vue({
  el: "#app",
  store,
  validations: {}
});



/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

// window.axios = require('axios');

// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


// let token = document.head.querySelector('meta[name="csrf-token"]');

// if (token) {
//     window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
// } else {
//     console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
// }
