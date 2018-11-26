<template>
    <div>
        <template v-for="post in posts">
            <Post :post="post"/>
        </template>

        <div class="d-flex ht-300 pos-relative align-items-center" v-if="loading" style="margin: 0 auto;">
            <div class="sk-cube-grid">
                <div class="sk-cube sk-cube1"></div>
                <div class="sk-cube sk-cube2"></div>
                <div class="sk-cube sk-cube3"></div>
                <div class="sk-cube sk-cube4"></div>
                <div class="sk-cube sk-cube5"></div>
                <div class="sk-cube sk-cube6"></div>
                <div class="sk-cube sk-cube7"></div>
                <div class="sk-cube sk-cube8"></div>
                <div class="sk-cube sk-cube9"></div>
            </div>
        </div>

    </div><!-- media -->
</template>


<script>
    import Post from './Post'
    import axios from 'axios'
    import _ from 'lodash'
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    const axiosInstance = axios.create({
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })


    export default {
        name: 'posts',
        props: {
            username: {
                type: String,
                required: false
            },
        },
        data() {
            return {
                posts: [],
                loading: true,
                offset: 10,
                total: 0
            }
        },
        components: {Post},
        methods: {
            onScroll: (e) => {
                console.log(e)
            }
        },
        async mounted() {
            let url;
            let secondUrl;
            if (this.username) {
                url = Routing.generate('api.posts.getPosts', {username: this.username})
                secondUrl = Routing.generate('api.posts.getMorePosts', {offset: this.offset, username: this.username})
            } else {
                url = Routing.generate('api.posts.getPosts')
                secondUrl = Routing.generate('api.posts.getMorePosts', {offset: this.offset})
            }
            // let secondUrl = Routing.generate('api.posts.getPosts')
            let {data} = await axiosInstance.get(url)

            this.posts = data.posts
            this.total = data.total
            this.loading = false

            window.addEventListener('scroll', async (e) => {
                if (((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) && this.offset < this.total) {
                    this.loading = true
                    let {data} = await axiosInstance.get(secondUrl)
                    console.log(data)
                    this.posts = [...this.posts, ...data]
                    this.offset += data.length
                    console.log(this.offset)

                    this.loading = false
                }
            })
        },

    }
</script>