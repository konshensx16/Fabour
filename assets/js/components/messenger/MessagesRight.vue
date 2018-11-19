<template>
    <div>
        <div class="messages-right d-none d-lg-block">
            <!--<h2 class="text-center mg-t-20" v-if="isUserEmpty">Welcome to the Messages area :)</h2>-->
            <div class="message-header" v-if="!isUserEmpty">
                <!--<div class="d-flex ht-300 pos-relative align-items-center" v-if="LOADING_USER">-->
                    <!--<div class="sk-three-bounce">-->
                        <!--<div class="sk-child sk-bounce1 bg-gray-800"></div>-->
                        <!--<div class="sk-child sk-bounce2 bg-gray-800"></div>-->
                        <!--<div class="sk-child sk-bounce3 bg-gray-800"></div>-->
                    <!--</div>-->
                <!--</div>-->
                <div class="media" v-if="!LOADING_USER">
                    <img :src="USER.avatar" alt="">
                    <div class="media-body">
                        <h6>{{ USER.username }}</h6>
                        <p>Last seen: {{ USER.last_seen }}</p>
                    </div><!-- media-body -->
                </div><!-- media -->
                <div class="message-option">
                    <div class="d-none d-sm-flex">
                        <a href=""><i class="icon ion-ios-gear-outline"></i></a>
                    </div>
                    <div class="d-sm-none">
                        <a href=""><i class="icon ion-more"></i></a>
                    </div>
                </div>
            </div><!-- message-header -->
            <div class="message-body ps ps--theme_default" ref="messagesBox">
                <div class="d-flex ht-300 pos-relative align-items-center" v-if="LOADING_MESSAGES">
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
                <div class="media-list">
                    <div class="media" v-for="message in MESSAGES">
                        <img :src="message.avatar" :alt="message.id" v-if="!message.mine">
                        <div class="media-body" v-bind:class="{ reverse : message.mine }">
                            <div class="msg">
                                <p>{{ message.content }}</p>
                            </div>
                        </div><!-- media-body -->
                        <img :src="message.avatar" :alt="message.id" v-if="message.mine">
                    </div><!-- media -->
                </div><!-- media-list -->
                <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                    <div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                </div>
                <div class="ps__scrollbar-y-rail" style="top: 0px; right: 0px;">
                    <div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                </div>
            </div><!-- message-body -->
            <div class="message-footer" v-if="!isUserEmpty">
                <div class="row row-sm">
                    <div class="col-9 col-sm-8 col-xl-9">
                        <input class="form-control" placeholder="Type something here..." type="text"
                               v-model="messageInput"
                               ref="message"
                               v-on:keyup.enter="publishMessage"
                               autofocus>
                    </div><!-- col-8 -->
                    <div class="col-3 col-sm-4 col-xl-3 tx-right">
                        <div class="d-none d-sm-block">
                            <a v-on:click.prevent="publishMessage"><i class="icon ion-ios-arrow-right"></i></a>
                        </div>
                    </div><!-- col-4 -->
                </div><!-- row -->
            </div><!-- message-footer -->
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import _ from 'lodash'

    let webSocket = WS.connect(_WS_URI)
    let session

    export default {
        name: 'messages-right',
        data() {
            return {
                messageInput: '',
            }
        },
        methods: {
            publishMessage() {
                // TODO: don't allow empty messages
                session.publish(`message/${this.conversation_id}`, {
                    'message': this.messageInput,
                    'recipient': this.user.username,
                    'sender': this.currentUser.username,
                    'avatar': this.currentUser.avatar, // this is wrong, my avatar should be there and not the recipient
                })
                // this is mine message, whioch means it should have the currentUser's avatar
                this.messages.push({
                    'content': this.messageInput, // check the messageTopic where $event['message'], that's why im not getting an array in here
                    'avatar': this.currentUser.avatar,
                    'mine': true
                })

                // TODO: scroll the last message into view
                let el = this.$refs.messagesBox
                el.scrollTop = el.scrollHeight

                // TODO: after publishing the message add it to the list of messages
                this.messageInput = ''
                this.$nextTick(() => {
                    // console.log(this.$refs.message.$el)
                })
            }
        },
        computed: {
            ...mapGetters([
                'MESSAGES',
                'LOADING_MESSAGES',
                'USER',
                'LOADING_USER'
            ]),
            isUserEmpty() {
                return _.isEmpty(this.USER)
            }
        },
        mounted() {
            this.$store.dispatch('GET_LATEST_MESSAGES', this.$route.params.id)
            this.$store.dispatch('GET_USER', this.$route.params.userId)
            this.conversationMessages = this.messages
            webSocket.on('socket/connect', (new_session) => {
                session = new_session
                session.subscribe(`message/${this.conversation_id}`, (uri, payload) => {
                    // TODO: push the new messages
                    this.messages.push({
                        'content': payload.msg, // check the messageTopic where $event['message'], that's why im not getting an array in here
                        'avatar': payload.avatar,
                        'mine': false
                    })

                    // TODO: scroll the last message into view
                    let el = this.$refs.messagesBox
                    el.scrollTop = el.scrollHeight
                })
            })

            webSocket.on('socket/disconnect', (error) => {
                let notification = new Notyf({
                    delay: 5000
                })
                console.log(error.reason + ' ' + error.code)
                notification.alert(error.reason + ' ' + error.code)
            })
        },
        updated: function () {
            this.$nextTick(() => {
                // TODO: scroll the last message into view
                let el = this.$refs.messagesBox
                el.scrollTop = el.scrollHeight
            })
        }
    }
</script>