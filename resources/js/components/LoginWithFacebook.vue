<template>
    <div></div>
</template>
<script>
import Auth from "../Auth";
export default {
    created() {
        this.fetchDataFromCallbackURL();
    },
    methods: {
        fetchDataFromCallbackURL() {
            let code = this.$route.query.code;
            axios.get('https://new-app.test/api/facebook/user?code='+code)
                .then(response => {
                    Auth.login(response.data.token, response.data.user);
                    this.$parent.loggedUser = response.data.user;
                    this.$router.push('/');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }
}
</script>
