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
                    <input class="form-control" placeholder="Type something here..." type="text">
                </div><!-- col-8 -->
                <div class="col-3 col-sm-4 col-xl-3 tx-right">
                    <div class="d-none d-sm-block">
                        <a href="#" :onclick="publishMessage()"><i class="icon ion-ios-mic-outline"></i></a>
                    </div>
                </div><!-- col-4 -->
            </div><!-- row -->
        </div><!-- message-footer -->
    </div>
</template>

<script>
    export default {
        name: 'messages-right',
        data() {
            return {
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
                    {
                        "_id": "5bd7f5eb2cd5ad285a91a791",
                        "content": "sunt ea occaecat amet consectetur nostrud dolore sit nostrud culpa cillum",
                        "mine": true
                    },
                    {
                        "_id": "5bd7f5eba2958f1aada8a22d",
                        "content": "sint reprehenderit duis eu sint amet incididunt do culpa aute laboris",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5eba3a2a09499ff2fb1",
                        "content": "mollit nisi exercitation do proident et ad quis aliquip ea ullamco",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5eb2466ea4e522088b8",
                        "content": "dolor cupidatat laborum elit excepteur aliquip elit cupidatat sit est nisi",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5eb7d4eb2ddb4d61cee",
                        "content": "exercitation proident veniam minim laboris id minim sit velit elit ex",
                        "mine": true
                    },
                    {
                        "_id": "5bd7f5ebba91c38081673cda",
                        "content": "irure cupidatat non duis laboris ipsum reprehenderit sit sit aute anim",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5ebbc16a592717c60e7",
                        "content": "veniam veniam id aliquip sit elit magna incididunt elit enim pariatur",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5ebe6bdf416b427e53c",
                        "content": "irure quis quis Lorem commodo sint do tempor velit Lorem do",
                        "mine": true
                    },
                    {
                        "_id": "5bd7f5eb524de2deec2fb524",
                        "content": "tempor enim labore qui elit officia adipisicing ipsum mollit esse veniam",
                        "mine": false
                    },
                    {
                        "_id": "5bd7f5ebb43e0cbed29df8ee",
                        "content": "id nisi proident et in adipisicing sint voluptate ad deserunt consequat",
                        "mine": true
                    },
                    {
                        "_id": "5bd7f5eb5dba6ce6d1eb4dde",
                        "content": "eiusmod officia proident dolore minim tempor veniam enim veniam id magna",
                        "mine": false
                    }
                ]
            }
        },
        methods: {
            loadShit: () => {
                console.log('hello world')
            },
            publishMessage: () => {
                console.log(this)
                console.log(this.$wamp)
                if (this.$wamp) {
                    this.$wamp.publish('message_topic', ['hello from vue']);

                }
            }
        },
        // wamp: {
        //     subscribe: {
        //         'message_topic'(args, kwArgs, details) {
        //             console.log(args)
        //             console.log(kwArgs)
        //             console.log(details)
        //         }
        //     }
        // },
        mounted() {
            // load the data and everything else here
            console.log(this)
            this.$wamp.publish('message_topic', ['hello from vue'])
            // this.$wamp.subscribe('message_topic', function(args, kwArgs, details) {
            //     // component context is available
            //     console.log(args)
            //     console.log(kwArgs)
            //     console.log(details)
            // }, {
            //     acknowledge: true // option needed for promise, automatically added
            // }).then(function(s) {
            //     console.log('AutobahnJS Subscription object: ', s);
            // });
        }
    }
</script>