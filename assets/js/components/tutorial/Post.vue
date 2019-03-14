<template>
    <div class="media media-demo mg-t-20 post-bg-img" v-bind:style="{ backgroundImage: 'url(' + post.thumbnail + ')' }">
        <div class="media-body mg-sm-t-0">
            <h5 class="tx-inverse mg-b-20">
                <a :href="generatePostUrl(post.id)">{{ post.title }}</a>
            </h5>
            <p>
                {{ post.content }}
            </p>
            <p>
                <em>
                    {{ post.created_at }}
                </em>
            </p>
        </div><!-- media-body -->
        <!--<img :src="getThumbnail" class="media-img-demo align-self-center" alt="Image">-->
    </div>
</template>

<script>
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    export default {
        props: {
            post: {
                required: true,
                type: Object
            }
        },
        methods: {
            generatePostUrl(parameter) {
                return Routing.generate('post.display', {uuid: parameter})
            },
        },
        mounted () {
            console.log(this.post)
        }
    }
</script>