<template>
    <div class="row">
        <div class="col-md-2" v-if="loggedUser">
            <div class="sidebar">
                <a class="active" href="#home">Dashboard</a>
                <a href="#home">Home</a>
                <a href="#">News</a>
                <a href="#">Contact</a>
                <a href="#">About</a>
            </div>
        </div>
        <div class="col-md-10">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark" v-if="loggedUser">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0)" @click="logout()" class="nav-item nav-link ml-3">Logout</a>
                    </li>
                </ul>
            </nav>
            <div class="container offset-1">
                <div class="mt-5">
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import Auth from "./Auth";
export default {
    data() {
        return {
            loggedUser: null
        };
    },
    created() {
      this.loggedUser = this.auth.user;
    },
    methods: {
        logout() {
            axios.get('https://new-app.test/api/logout')
                .then(({data}) => {
                    this.loggedUser = false;
                    Auth.logout();
                    this.$router.push('/login');
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    }

}
</script>

<style>
/* The side navigation menu */
.sidebar {
    margin: 0;
    padding: 0;
    width: 200px;
    background-color: #212529;
    position: fixed;
    height: 100%;
    overflow: auto;
}

/* Sidebar links */
.sidebar a {
    display: block;
    color: #C1C3C4;
    padding: 16px;
    text-decoration: none;
}

/* Active/current link */
.sidebar a.active {
    //background-color: #04AA6D;
    color: #FFFFFF;
}

/* Links on mouse-over */
.sidebar a:hover:not(.active) {
    color: white;
}

/* Page content. The value of the margin-left property should match the value of the sidebar's width property */
div.content {
    margin-left: 200px;
    padding: 1px 16px;
    height: 1000px;
}

/* On screens that are less than 700px wide, make the sidebar into a topbar */
@media screen and (max-width: 700px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .sidebar a {float: left;}
    div.content {margin-left: 0;}
}

/* On screens that are less than 400px, display the bar vertically, instead of horizontally */
@media screen and (max-width: 400px) {
    .sidebar a {
        text-align: center;
        float: none;
    }
}
</style>
