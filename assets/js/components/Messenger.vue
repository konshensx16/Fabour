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
        props: ['user', 'conversations', 'conversation_id'],
        components: {MessagesLeft, MessagesRight},
    }
</script>