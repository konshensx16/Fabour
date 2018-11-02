<template>
    <div class="messages-right d-none d-lg-block">
        <div class="message-header">
            <a href="" class="message-back"><i class="fa fa-angle-left"></i></a>
            <div class="media">
                <img src="http://via.placeholder.com/500x500" alt="">
                <div class="media-body">
                    <h6>{{ name }}</h6>
                    <p>Last seen: {{ lastSeen }}</p>
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
                <div class="media" v-for="message in messages">
                    <img src="http://via.placeholder.com/500x500" :alt="message.id" v-if="!message.mine">
                    <div class="media-body" v-bind:class="{ reverse : message.mine }">
                        <div class="msg">
                            <p>{{ message.content }}</p>
                        </div>
                    </div><!-- media-body -->
                    <img src="http://via.placeholder.com/500x500" :alt="message.id" v-if="message.mine">
                </div><!-- media -->
            </div><!-- media-list -->
            <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                <div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
            </div>
            <div class="ps__scrollbar-y-rail" style="top: 0px; right: 0px;">
                <div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
        </div><!-- message-body -->
        <div class="message-footer">
            <div class="row row-sm">
                <div class="col-9 col-sm-8 col-xl-9">
                    <input class="form-control" placeholder="Type something here..." type="text" v-model="messageInput" ref="messageBox">
                </div><!-- col-8 -->
                <div class="col-3 col-sm-4 col-xl-3 tx-right">
                    <div class="d-none d-sm-block">
                        <a href="#" v-on:click="publishMessage"><i class="icon ion-ios-mic-outline"></i></a>
                    </div>
                </div><!-- col-4 -->
            </div><!-- row -->
        </div><!-- message-footer -->
    </div>
</template>

<script>
    let webSocket = WS.connect(_WS_URI)
    let session

    console.log(this.session)

    export default {
        name: 'messages-right',
        data() {
            return {
                messageInput: '',
                name: 'Mohammed baza',
                lastSeen: '1 min ago',
                messages: [
                    {
                        "_id": "5bd7f5ebd2b3e88fff6e63c9",
                        "content": "exercitation est proident in deserunt ex est culpa tempor consectetur et",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5ebdd955e7d60621b96",
                        "content": "anim ut sit consequat quis irure consectetur do commodo officia consectetur",
                        "mine": true
                    },
                    {
                        "_id": "5bd7f5ebefa9121fe6d38929",
                        "content": "incididunt velit nostrud amet cupidatat mollit dolor pariatur id cupidatat fugiat",
                        "mine": false
                    },
                ]
            }
        },
        methods: {
            loadShit () {
                console.log(this)
            },
            publishMessage () {
                session.publish('message/channel', [this.messageInput])
                // TODO: after publishing the message add it to the list of messages
                this.messageInput = ''
                // this.$refs.messageBox.$el.focus()
            }
        },
        mounted() {
            webSocket.on('socket/connect', (new_session) => {
                session = new_session
                session.subscribe('message/channel', (uri, payload) => {
                    console.log(payload)
                    // TODO: push the new messages
                    this.messages.push({
                        'id': '28282',
                        'content': payload.msg, // check the messageTopic where $event[0], that's why im not getting an array in here
                        'mine': true
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