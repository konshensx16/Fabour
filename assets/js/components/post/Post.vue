<template>
    <div class="media media-demo mg-t-20">
        <div class="media-body mg-sm-t-0">
            <h5 class="tx-inverse mg-b-20">
                <a :href="generatePostUrl(post.id)">{{ post.title }}</a>
            </h5>
            <p>
                {{ getContent }}
            </p>
            <p>
                <strong>
                    <a :href="generateProfileUrl(post.username)">{{ post.username }}</a>
                    in
                    <a :href="generateCategoryUrl(post.slug)">{{ post.name }}</a>
                </strong>
                <br>
                <em>
                    {{ post.created_at }}
                </em>
            </p>
        </div><!-- media-body -->
        <img :src="getThumbnail" class="media-img-demo align-self-center" alt="Image">
    </div>
</template>

<script>
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    export default {
        name: 'post',
        props: {
            post: Object
        },
        methods: {
            generatePostUrl(parameter) {
                return Routing.generate('post.display', {id: parameter})
            },
            generateProfileUrl(parameter) {
                return Routing.generate('profile.userProfile', {'username': parameter})
            },
            generateCategoryUrl(parameter) {
                return Routing.generate('category.category', {'slug': parameter})
            }
        },
        computed: {
            getContent() {
                if (this.post.content) {
                    return this.post.content.length > 300 ? this.post.content.slice(0, 300) + '...' : this.post.content
                }
            },
            getThumbnail() {
                if (this.post.thumbnail) {
                    return this.post.thumbnail
                }
                return '/assets/img/img0.jpg'
            }
        },
        mounted() {
            console.log(this.post)
        }
    }
</script>