import Vue from 'vue'
import VueWamp from 'vue-wamp'
import Messenger from './components/Messenger' 

Vue.use(VueWamp, {
    debug: true,
    url: _WS_URI, // TODO: get the url just like before
    realm: 'realm1',
    onopen: (session, details) => {
        console.log('WAMP connected', session, details)
    },
    onclose: (session, details) => {
        console.log('WAMP closed', session, details)
    }
})

new Vue({
    el: '#messenger',
    components: {Messenger}
})