import Vue from 'vue';
import Auth from "./Auth";
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Login from "./components/Login.vue";
import LoginWithFacebook from "./components/LoginWithFacebook.vue";
import Dashboard from "./components/Dashboard.vue";

const routes = [
    {
        path: '/login',
        component: Login,
        name: 'login'
    },
    {
        path: '/api/auth/facebook/callback',
        component: LoginWithFacebook,
        name: 'LoginWithFacebook',
    },
    {
        path: '/',
        component: Dashboard,
        name: 'Dashboard',
        meta: {
            requiresAuth: true
        }
    },

]

const router = new VueRouter({
    mode: 'history', // Set mode to 'history'
    base: process.env.BASE_URL,
    routes
});

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth) ) {
        if (!Auth.check()) {
            router.push('/login');
        } else {
            next();
        }
    } else {
        next();
    }
    return;
});


export default router;
