<template>
	<div>
		<MessagesLeft :conversations="conversations"/>
		<MessagesRight :user="user" :conversation_id="conversation_id"/>
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
            conversations: {
			    required: true,
                type: [Array]
			},
            conversation_id: {
				type: Number,
				default: 0
			}
		},
        components: {MessagesLeft, MessagesRight},
    }
</script>