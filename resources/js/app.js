require('./bootstrap');
import Vue from 'vue';
import router from './routes';
import App from "./app.vue";
import Auth from "./Auth";

Vue.prototype.auth = Auth;


new Vue({
    router, render: h => h(App),
}).$mount('#app');
