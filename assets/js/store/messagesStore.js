import axios from 'axios'
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

const routes = require('../routes.json');

Routing.setRoutingData(routes)

const axiosInstance = axios.create({
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})

export default  {
    state: {
        conversations: [],
        messages: [],
        loadingConversations: true,
        loadingMessages: true
    },
    getters: {
        CONVERSATIONS: state => {
            return state.conversations
        },
        LOADING_CONVERSATIONS: state => {
            return state.loadingConversations
        },
        MESSAGES: state => {
            return state.messages
        },
        LOADING_MESSAGES: state => {
            return state.loadingMessages
        }
    },
    mutations: {
        SET_CONVERSATIONS: (state, payload) => {
            state.conversations = payload
        },
        SET_LOADING_CONVERSATIONS: (state, payload) => {
            state.loadingConversations = payload
        },
        SET_MESSAGES: (state, payload) => {
            state.messages = payload
        },
        SET_LOADING_MESSAGES: (state, payload) => {
            state.loadingMessages = payload
        },
        NEW_MESSAGE: (state, payload) => {
            state.messages.push(payload)
        }
    },
    actions: {
        GET_CONVERSATIONS: async (context, payload) => {

            let url = Routing.generate('messages.conversations')

            let { data } = await axiosInstance.get(url)
            context.commit('SET_CONVERSATIONS', data[0])
            context.commit('SET_LOADING_CONVERSATIONS', false)
        },
        GET_LATEST_MESSAGES: async (context, payload) => {
            // TODO: generate the correct url (expose in the controller)
            let url = Routing.generate('messages.latestMessages', {'conversation_id' : payload})
            let { data } = await axiosInstance.get(url)
            context.commit('SET_MESSAGES', data[0])
            context.commit('SET_LOADING_MESSAGES', false)
        },
        ADD_MESSAGE: (context, payload) => {
            context.commit('NEW_MESSAGE', payload)
        }
    }
}