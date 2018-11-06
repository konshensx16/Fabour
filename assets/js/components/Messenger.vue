<template>
    <div>
        <MessagesLeft :conversations="conversations"/>
        <MessagesRight
                :current-user="currentUser"
                :user="user"
                :conversation_id="conversation_id"
                :messages="messages"
                />
    </div>
</template>

<script>
    import MessagesLeft from './messenger/MessagesLeft'
    import MessagesRight from './messenger/MessagesRight'

    export default {
        name: "messenger",
        props: {
            user: {
                type: Object,
                default: () => {
                    return {}
                }
            },
            currentUser: {
                type: Object,
            },
            conversations: {
                required: true,
                type: [Array]
            },
            conversation_id: {
                type: Number,
                default: 0
            },
            messages: {
                required: false,
                type: Array
            }
        },
        components: {MessagesLeft, MessagesRight},
        created() {
            console.log(this.currentUser)
            console.log(this.user)
        }
    }
</script>