import Vue from 'vue'
import Vuex from 'vuex'
import messagesModule from './messagesStore'
import userStore from './userStore'

Vue.use(Vuex)

export const store = new Vuex.Store({
    modules: {
        messages: messagesModule,
        user: userStore
    }
})