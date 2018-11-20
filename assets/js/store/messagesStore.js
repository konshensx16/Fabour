import axios from 'axios'
import _ from 'lodash'
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
        },
        SET_CONVERSATION_TO_READ: (state, payload) => {
            // NOTE: unread is the 'count'
            // NOTE: payload will be the conv_id
            let indexOf = _.findIndex(state.conversations, (o) => {
                return o.id === payload
            })
            state.conversations[indexOf].count = 0
        },
        SET_MESSAGE_AS_LAST: (state, payload) => {
            // NOTE: conversation.message IS THE latestMessage in the list, which i should replace
            let indexOf = _.findIndex(state.conversations, (o) => {
                return o.id === payload.id
            })
            let conversation = state.conversations[indexOf]
            conversation.message = payload.message
            conversation.date = 'Just now'
            // TODO: inc the count of messages
            if (conversation.count >= 0 && conversation.count < 10 && payload.inc) {
                conversation.count++
            }
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
        },
        MARK_AS_READ: (context, payload) => {
            context.commit('SET_CONVERSATION_TO_READ', payload)
        },
        UPDATE_CONVERSATION_LATEST_MESSAGE: (context, payload) => {
            context.commit('SET_MESSAGE_AS_LAST', payload)
        }
    }
}