<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card mt-5">
                    <div class="card-header text-center">Login With Facebook</div>
                    <div class="card-body">
                        <div class="row mt-1">
                            <div class="col-md-8 offset-md-3">
                                <a class="btn btn-primary" @click="loginWithFacebook">
                                    <i class="fa fa-facebook"></i> Login with Facebook
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    setup() {
        const loginWithFacebook = async () => {
            axios.get('https://new-app.test/api/auth/facebook/redirect')
            .then(response => {
                window.location.href = response.data.url;
            });
        };

        return {
            loginWithFacebook,
        };
    },
};
</script>
