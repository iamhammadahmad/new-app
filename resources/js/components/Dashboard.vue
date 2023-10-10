<template>
    <div>
        <div class="row">
            <!-- Button trigger modal -->
            <div class="offset-8">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Upload Video
                </button>
            </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Upload</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" class="form-control" v-model="title" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" v-model="description" rows="3" required>

                                    </textarea>
                                </div>
                                <div class="form-group">
                                    <label for="video">Video</label>
                                    <input type="file" class="form-control" ref="video" accept="video/*" required>
                                </div>
                                <br>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="uploadVideo">
                                Post Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div><br>
        <div class="row">
            <div class="col-md-10">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Title</th>
                            <th scope="col">Video</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody v-if="reels.length">
                        <tr v-for="reel in reels">
                            <th scope="row">1</th>
                            <td>{{ reel.title }}</td>
                            <td>{{ reel.video }}</td>
                            <td><a class="btn" @click="publishVideo(reel.id)">Publish to Facebook</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import Auth from "../Auth";
export default {
    data() {
        return {
            title: '',
            description: '',
            reels: []
        };
    },
    mounted() {
        this.list();
    },
    methods: {
        list(){
            axios.get('https://new-app.test/api/reels')
                .then((response) => {
                    this.reels = response.data.reels;
                    console.log(this.reels.length);
                });

        },
        uploadVideo() {
            console.log('clicked');
            const config = {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'content-type': 'multipart/form-data'
                }
            }

            let formData = new FormData();
            formData.append('title', this.title);
            formData.append('description', this.description);
            formData.append('video', this.$refs.video.files[0]);

            axios.post('https://new-app.test/api/upload-video', formData)
                .then((response) => {
                    // this.success = response.data.success;
                    this.list();
                })
                .catch(function (error) {
                    this.output = error;
                });
        },
        publishVideo(id){
            axios.get(`https://new-app.test/api/video/${id}/post`)
                .then((response) => {
                    console.log(response.message);
                });

        }
    },
};
</script>
