<template>
    <div>
        <div class="messages-right d-none d-lg-block">
            <h2 class="text-center mg-t-20" v-if="isUserEmpty">Welcome to the Messages area :)</h2>
            <div class="message-header" v-if="!isUserEmpty">
                <a href="" class="message-back"><i class="fa fa-angle-left"></i></a>
                <div class="media">
                    <img :src="user.avatar" alt="">
                    <div class="media-body">
                        <h6>{{ user.username }}</h6>
                        <p>Last seen: {{ user.last_seen }}</p>
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
            <div class="message-body ps ps--theme_default">
                <div class="media-list">
                    <div class="media" v-for="message in conversationMessages">
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
    let webSocket = WS.connect(_WS_URI)
    let session

    export default {
        name: 'messages-right',
        props: {
            user: {
                type: Object,
                default: () => {
                    return {}
                }
            },
            currentUser: {
                type: Object
            },
            conversation_id: {
                required: false,
                type: Number
            },
            messages: {
                required: false,
                type: Array
            }
        },
        data() {
            return {
                messageInput: '',
                conversationMessages: []
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

                // TODO: after publishing the message add it to the list of messages
                this.messageInput = ''
                this.$nextTick(() => {
                    // console.log(this.$refs.message.$el)
                })
            }
        },
        computed: {
            isUserEmpty() {
                return Object.keys(this.user).length === 0
            }
        },
        mounted() {
            console.log('User', this.user)
            console.log('CurrentUser', this.currentUser)

            console.log(this.conversationMessages)
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
                })
            })

            webSocket.on('socket/disconnect', (error) => {
                let notification = new Notyf({
                    delay: 5000
                })
                console.log(error.reason + ' ' + error.code)
                notification.alert(error.reason + ' ' + error.code)
            })
        }
    }
</script>