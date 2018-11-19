import Vue from 'vue'
import VueRouter from 'vue-router'
import Messenger from './components/Messenger'
import MessagesRight from './components/messenger/MessagesRight'
import {store} from './store/store'

Vue.use(VueRouter)

let $messenger = document.querySelector('#messenger')

const routes = [
    { path: '/con/:id', component: MessagesRight, name: 'conversation'}
]

const router = new VueRouter({
    mode: 'history',
    routes,
    base: $messenger.getAttribute('data-base')
})

new Vue({
    el: '#messenger',
    components: {Messenger},
    store,
    router
})