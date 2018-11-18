import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'
const routes = require('../routes.json');

Routing.setRoutingData(routes)
Vue.use(Vuex)

const axiosInstance = axios.create({
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})

export const store = new Vuex.Store({
    state: {
        conversations: []
    },
    getters: {
        CONVERSATIONS: state => {
            return state.conversations
        }
    },
    mutations: {
        SET_CONVERSATIONS: (state, payload) => {
            state.conversations = payload
        }
    },
    actions: {
        GET_CONVERSATIONS: async (context, payload) => {

            let url = Routing.generate('messages.conversations')

            let { data } = await axiosInstance.get(url)
            context.commit('SET_CONVERSATIONS', data[0])
        }
    }
})