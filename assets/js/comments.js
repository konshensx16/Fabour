import Vue from 'vue'
import Comments from './components/comment/Comments'
import store from './store/commentsStore'

console.log("Hello from comment")

new Vue({
    el: '#comments',
    components: {Comments},
    store: store
})