<template>
    <li class="media" :id="comment.comment_id" :class="{ faded : loading }">
        <img class="media-img blocked" :src="comment.avatar" alt="">

        <div class="media-body">
            <h6 class="tx-inverse mg-b-10">
                <a :href="generateProfileUrl(comment.username)">
                    {{ comment.username }}
                </a>
                <small>{{ comment.created_at }}</small>
                <div class="dropdown dropdown-c pull-right">
                    <a href="#" class="logged-user" data-toggle="dropdown">
                        <i class="fa fa-ellipsis-h"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <nav class="nav">
                            <a :href="editUrl" class="nav-link"><i class="icon ion-edit"></i> Edit</a>
                            <a href="#" @click.prevent="deleteComment()" class="nav-link text-danger"><i
                                    class="icon ion-trash-a"></i> Delete</a>
                        </nav>
                    </div><!-- dropdown-menu -->
                </div><!-- dropdown -->
            </h6>


            {{ comment.content }}

            <div class="blocked-ui">

            </div>
        </div>

    </li>
</template>

<script>
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'
    import axios from 'axios'

    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    export default {
        name: 'comment',
        props: {
            comment: Object
        },
        data() {
          return {
              loading: false
          }
        },
        methods: {
            generateProfileUrl(parameter) {
                return Routing.generate('profile.userProfile', {'username': parameter})
            },
            async deleteComment() {
                if (!confirm("Do you want to remove this comment?")) return false
                this.loading = true
                await this.$store.dispatch('DELETE_COMMENT', {
                    commentId: this.comment.comment_id
                })
                this.loading = false
            }
        },
        computed: {
            editUrl() {
                return Routing.generate('api.comment.update', {'id': this.comment.comment_id})
            },
            deleteUrl() {
                return Routing.generate('api.comment.delete', {'id': this.comment.comment_id})
            }
        }
    }
</script>

<style>
    .blocked-ui {
        position: absolute;
        height: 100%;
        width: 100%;
        /*background-color: rgba(255, 255, 255 .5);*/
        background-color: black;
    }
    .faded {
        opacity: .5;
    }
</style>