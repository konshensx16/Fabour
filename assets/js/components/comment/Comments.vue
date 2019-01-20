<template>
    <div>
        <label class="section-title">{{ total }} Comments</label>
        <!--<h2 v-if="!isPostsEmpty" class="text-center">There are no posts at the moment</h2>-->



        <ul class="media-list-demo">
            <!--{% for comment in post.comments %}-->
            <template v-for="comment in comments">
                <Comment :comment="comment" />
            </template>
        </ul>
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
    import Comment from './Comment'
    import axios from 'axios'
    import _ from 'lodash'
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    const axiosInstance = axios.create({
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })


    export default {
        name: 'comments',
        props: {
            post_id: {
                type: String,
                required: true
            },
        },
        data() {
            return {
                comments: [],
                loading: true,
                offset: 10,
                total: 0
            }
        },
        computed: {
            isPostsEmpty: function () {
                return !!parseInt(this.comments.length)
            }
        },
        components: {Comment},
        methods: {
            onScroll: (e) => {
                console.log(e)
            }
        },
        async mounted() {
            let url;
            let secondUrl;

            url = Routing.generate('api.comment.getCommentsForPost', {uuid: this.post_id})
            // let secondUrl = Routing.generate('api.comments.getPosts')
            let {data} = await axiosInstance.get(url)
            // console.log(data)
            this.comments = data.comments
            this.total = data.total
            this.loading = false
            secondUrl = Routing.generate('api.comment.getMoreCommentsForPost', {offset: this.offset, uuid: this.post_id})

            window.addEventListener('scroll', async (e) => {
                if (((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) && this.offset < this.total) {
                    this.loading = true
                    let {data} = await axiosInstance.get(secondUrl)
                    this.comments = [...this.comments, ...data]
                    this.offset += data.length
                    this.loading = false
                }
            })
        },
    }
</script>