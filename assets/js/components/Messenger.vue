<template>
	<div>
		<MessagesLeft/>
		<MessagesRight/>
	</div>
</template>	

<script>
	import MessagesLeft from './messenger/MessagesLeft'
	import MessagesRight from './messenger/MessagesRight'

	export default {
		name: "messenger",
		components: {MessagesLeft, MessagesRight}
	}
</script>