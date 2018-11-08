<template>
    <div class="messages-left">
        <div class="slim-pageheader">
            <h6 class="slim-pagetitle">Messages</h6>
            <a v-on:click.prevent="toggle" class="messages-compose"><i class="icon ion-compose"></i></a>
        </div><!-- slim-pageheader -->
        <div class="messages-list ps ps--theme_default ps--active-y">
            <NewConversation v-if="isOpen"/>
            <a class="media single" :href="'con/' + conversation.id"  v-for="conversation in this.conversations" :id="conversation.id" v-bind:class="{unread : conversation.isActive}">
                <div class="media-left">
                    <img :src="conversation.avatar" alt="">
                    <!--<span class="square-10 bg-success"></span>-->
                </div><!-- media-left -->
                <div class="media-body">
                    <div>
                        <h6>{{ conversation.username }}</h6>
                        <p>{{ conversation.message }}</p>
                    </div>
                    <div>
                        <span>{{ conversation.date }}</span>
                    </div>
                </div><!-- media-body -->
            </a><!-- media -->
            <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__scrollbar-y-rail" style="top: 0px; height: 687px; right: 0px;"><div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 643px;"></div></div></div><!-- messages-list -->
        <!--<div class="messages-left-footer">-->
            <!--<button class="btn btn-slim btn-uppercase-sm btn-block">Load Older Messages</button>-->
        <!--</div>&lt;!&ndash; messages-left-footer &ndash;&gt;-->
    </div>
</template>

<script>
    import NewConversation from './NewConversation'

    export default {
        name: 'messages-left',
        components: {NewConversation},
        props: {
            conversations: {
                required: true,
                type: Array
            }
        },
        data () {
            return {
                isOpen: !false
            }
        },
        methods: {
            toggle () {
                this.isOpen = !this.isOpen
            }
        }
    }
</script>