<template>
    <div class="messages-left">
        <div class="slim-pageheader">
            <h6 class="slim-pagetitle">Messages</h6>
            <a v-on:click.prevent="toggle" class="messages-compose"><i class="icon ion-compose"></i></a>
        </div><!-- slim-pageheader -->
        <div class="messages-list ps ps--theme_default ps--active-y">
            <NewConversation v-if="isOpen"/>
            <div class="d-flex ht-300 pos-relative align-items-center" v-if="loading">
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
            <router-link class="media single"
                         :to="{name: 'conversation', params: {id: conversation.id, userId: conversation.user_id}}"
                         v-for="(conversation, key, index) in CONVERSATIONS" :key="key"
                         :id="conversation.id" v-bind:class="{unread : conversation.count > 0}">
                <div class="media-left">
                    <img :src="conversation.avatar" alt="">
                    <span class="square-10 bg-success"></span>
                </div><!-- media-left -->
                <div class="media-body">
                    <div>
                        <h6>{{ conversation.username }}</h6>
                        <p>{{ conversation.message.length > 60 ? conversation.message.slice(0, 57) + '...' :
                            conversation.message }}</p>
                    </div>
                    <div>
                        <span>{{ conversation.date }}</span>
                        <span v-if="conversation.count > 0">{{ unreadMessagesCounter(conversation) }}</span>
                    </div>
                </div><!-- media-body -->
            </router-link><!-- media -->
            <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                <div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
            </div>
            <div class="ps__scrollbar-y-rail" style="top: 0px; height: 687px; right: 0px;">
                <div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 643px;"></div>
            </div>
        </div><!-- messages-list -->
        <!--<div class="messages-left-footer">-->
        <!--<button class="btn btn-slim btn-uppercase-sm btn-block">Load Older Messages</button>-->
        <!--</div>&lt;!&ndash; messages-left-footer &ndash;&gt;-->
    </div>
</template>

<script>
    import {playSound} from '../../audio'
    import NewConversation from './NewConversation'
    import {mapGetters} from 'vuex'
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

    const routes = require('../../routes.json');
    let webSocket = WS.connect(_WS_URI)
    let session

    // NOTE: this was in mounted but ti didn't worked after adding the router and everything, so it's here now
    webSocket.on('socket/connect', (new_session) => {
        session = new_session
    })


    webSocket.on('socket/disconnect', (error) => {
        let notification = new Notyf({
            delay: 5000
        })
        notification.alert(error.reason + ' ' + error.code)
    })


    Routing.setRoutingData(routes)

    export default {
        name: 'messages-left',
        components: {NewConversation},
        data() {
            return {
                isOpen: !true,
                conversations: [],
                loading: true
            }
        },
        computed: {
            // mix the getters into computed with object spread operator
            ...mapGetters([
                'CONVERSATIONS',
                'CURRENT_USER'
            ])
        },
        methods: {
            unreadMessagesCounter: function (conversation) {
                return conversation.count >= 10 ? '10+' : conversation.count
            },
            toggle() {
                this.isOpen = !this.isOpen
            }
        },
        mounted() {
            this.loading = true
            this.$store.dispatch('GET_CONVERSATIONS')
            this.loading = false
            let promise = this.$store.dispatch('GET_CURRENT_USER')
            promise.then(() => {
                session.subscribe(`conversation/${this.CURRENT_USER.username}`, (uri, payload) => {
                    this.$store.dispatch('UPDATE_CONVERSATION_LATEST_MESSAGE', payload)
                    playSound()
                })
            })
        },
        created() {
        }
    }
</script>