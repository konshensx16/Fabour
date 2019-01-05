<template>
    <div>
        <div class="messages-right d-none d-lg-block">
            <div class="message-header" v-if="!isUserEmpty">
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
                    </div><div class="d-none d-sm-flex">
                        <a href="#" v-on:click.prevent="removeConversation()"><i class="icon ion-ios-trash-outline red"></i></a>
                    </div>
                    <div class="d-sm-none">
                        <a href=""><i class="icon ion-more"></i></a>
                    </div>
                </div>
            </div><!-- message-header -->
            <div class="message-body ps ps--theme_default" ref="messagesBox" v-on:scroll="onScroll">
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
                               autofocus
                               v-bind:disabled="isConnected">
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

    export default {
        name: 'messages-right',
        data() {
            return {
                messageInput: '',
            }
        },
        methods: {
            removeConversation() {
                // TODO: send a message to remove, maybe i should display a confirmation messages
                if (confirm('Are you sure you want to remove the conversation, this operation cannot be undone')) {
                    console.log("Alright will be removed right now ")
                }
            },
            async onScroll(e) {
                if (e.target.scrollTop === 0) {
                    if (this.currentMessagesCount < this.totalMessages) {
                        let initialHeight = e.target.scrollHeight
                        await this.$store.dispatch('GET_PREVIOUS_MESSAGES', {id: this.$route.params.id})
                        this.$nextTick(() => {
                            this.$el.querySelector('.message-body').scrollTop = e.target.scrollHeight - initialHeight
                        })
                    } else {
                        // TODO: remove the scrolling listener other wise it's gonna keep executing some random code
                    }
                }
            },
            scrollDown() {
                this.$nextTick(() => {
                    this.$el.querySelector('.message-body').scrollTop = this.$el.querySelector('.message-body').scrollHeight
                })
            },
            publishMessage() {
                // TODO: don't allow empty messages
                session.publish(`message/${this.$route.params.id}`, {
                    'message': this.messageInput,
                    'recipient': this.USER.username,
                    'sender': this.CURRENT_USER.username,
                    'avatar': this.CURRENT_USER.avatar, // this is wrong, my avatar should be there and not the recipient
                    'conversation_id': this.$route.params.id
                })
                session.publish(`conversation/${this.USER.username}`, {
                    id: this.$route.params.id,
                    message: this.messageInput,
                    sender: this.CURRENT_USER.username,
                    // the inc is by default true in the server side
                })
                let messageObj = {
                    'content': this.messageInput, // check the messageTopic where $event['message'], that's why im not getting an array in here
                    'avatar': this.CURRENT_USER.avatar,
                    'mine': true,
                    'id': this.$route.params.id
                }
                this.$store.dispatch('ADD_MESSAGE', messageObj)
                this.$store.dispatch('UPDATE_CONVERSATION_LATEST_MESSAGE', {
                    message: this.messageInput,
                    id: this.$route.params.id,
                    inc: false
                })
                this.scrollDown()
                // clear the input
                this.messageInput = ''
            },
            async getMessages() {
                await this.$store.dispatch('GET_MESSAGES', this.$route.params.id)
                if (this.MESSAGES) {
                    this.scrollDown()
                }
            }
        },
        computed: {
            ...mapGetters([
                'LOADING_MESSAGES',
                'USER',
                'LOADING_USER',
                'CURRENT_USER',
            ]),
            MESSAGES() {
                return this.$store.getters.MESSAGES(this.$route.params.id)
            },
            isUserEmpty() {
                return _.isEmpty(this.USER)
            },
            isConnected() {
                return this.session
            },
            totalMessages() {
                return this.$store.getters.CONVERSATION(this.$route.params.id).total
            },
            currentMessagesCount() {
                return this.$store.getters.MESSAGES(this.$route.params.id).length
            }
        },
        mounted() {
            this.getMessages()
            this.$store.dispatch('MARK_AS_READ', this.$route.params.id)
            this.$store.dispatch('GET_USER', this.$route.params.userId)
            this.$store.dispatch('GET_CURRENT_USER')
            if (session) {
                session.subscribe(`message/${this.$route.params.id}`, (uri, payload) => {
                    let messageObj = {
                        'content': payload.msg, // check the messageTopic where $event['message'], that's why im not getting an array in here
                        'avatar': payload.avatar,
                        'mine': false,
                        'id' : this.$route.params.id
                    }
                    this.$store.dispatch('ADD_MESSAGE', messageObj)
                    // scroll the message to the view
                    this.scrollDown()
                })
            }
        },
        updated (){
            this.scrollDown()

        }
    }
</script>