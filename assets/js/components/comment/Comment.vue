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
        }
    }
</script>