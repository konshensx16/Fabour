<template>
    <li class="media" :id="comment.comment_id">
        <img class="media-img" :src="comment.avatar" alt="">

        <div class="media-body">
            <h6 class="tx-inverse mg-b-10">
                <a :href="generateProfileUrl(comment.username)">
                    {{ comment.username }}
                </a>
                <small>{{ comment.created_at }}</small>
            </h6>
            {{ comment.content }}
        </div>
    </li>
    <!--<div class="media media-demo mg-t-20 post-bg-img" v-bind:style="{ backgroundImage: 'url(' + getThumbnail + ')' }">-->
        <!--<div class="media-body mg-sm-t-0">-->
            <!--<h5 class="tx-inverse mg-b-20">-->
                <!--<a :href="generatePostUrl(post.id)">{{ post.title }}</a>-->
            <!--</h5>-->
            <!--<p>-->
                <!--{{ getContent }}-->
            <!--</p>-->
            <!--<p>-->
                <!--<strong>-->
                    <!--<a :href="generateProfileUrl(post.username)">{{ post.username }}</a>-->
                    <!--in-->
                    <!--<a :href="generateCategoryUrl(post.slug)">{{ post.name }}</a>-->
                <!--</strong>-->
                <!--<br>-->
                <!--<em>-->
                    <!--{{ post.created_at }}-->
                <!--</em>-->
            <!--</p>-->
        <!--</div>&lt;!&ndash; media-body &ndash;&gt;-->
        <!--<img :src="getThumbnail" class="media-img-demo align-self-center" alt="Image">-->
    <!--</div>-->
</template>

<script>
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    export default {
        name: 'comment',
        props: {
            comment: Object
        },
        methods: {
            generateProfileUrl(parameter) {
                return Routing.generate('profile.userProfile', {'username': parameter})
            }
        },
        computed: {

        },
        mounted() {
        }
    }
</script>