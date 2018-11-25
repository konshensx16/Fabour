import Vue from 'vue'
import Posts from './components/post/Posts'
import {store} from './store/store'

new Vue({
    el: '#posts',
    components: {Posts},
    store,
})