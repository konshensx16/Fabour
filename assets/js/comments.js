import Vue from 'vue'
import Comments from './components/comment/Comments'
import store from './store/commentsStore'

new Vue({
    el: '#comments',
    components: {Comments},
    store: store
})