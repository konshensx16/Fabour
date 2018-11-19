<template>
    <div>
        <MessagesLeft/>
        <router-view :key="$route.fullPath"></router-view>
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
            }
        },
        components: {MessagesLeft, MessagesRight},
    }
</script>