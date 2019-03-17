<template>
    <div>
        <label class="section-title">{{ TOTAL }} Comments</label>
        <ul class="media-list-demo">
            <!--{% for comment in post.comments %}-->
            <h2 v-if="isEmpty" class="text-center">It's quite down here!</h2>
            <template v-if="!isEmpty" v-for="comment in COMMENTS">
                <Comment :comment="comment"/>
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
    import { mapGetters } from 'vuex'
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

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
                loading: true,
            }
        },
        computed: {
            ...mapGetters(['COMMENTS', 'TOTAL', 'OFFSET', 'CURRENT_USER']),
            isEmpty() {
                return this.COMMENTS.length <= 0
            }
        },
        components: {Comment},
        methods: {
            onScroll: (e) => {
                console.log(e)
            }
        },
        async mounted() {
            await this.$store.dispatch('GET_CURRENT_USER');
            await this.$store.dispatch('GET_COMMENTS', {
                postId: this.post_id
            })
            this.loading = false
            window.addEventListener('scroll', async (e) => {
                if (((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) && this.OFFSET < this.TOTAL) {
                    this.loading = true
                    await this.$store.dispatch('GET_MORE_COMMENTS',
                        {postId: this.post_id})
                    this.loading = false
                }
            })
        },
    }
</script>