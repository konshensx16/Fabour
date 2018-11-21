import axios from 'axios'
import _ from 'lodash'
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

const routes = require('../routes.json');

Routing.setRoutingData(routes)

const axiosInstance = axios.create({
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})

export default {
    state: {
        conversations: [],
        loadingMessages: true,
        offset: 0
    },
    getters: {
        CONVERSATIONS: state => {
            return state.conversations
        },
        CONVERSATION: state => id => {
            return state.conversations[id]
        },
        LOADING_MESSAGES: state => {
            return state.loadingMessages
        },
        MESSAGES: state => id => {
            return state.conversations[id].messages || {}
        }
    },
    mutations: {
        SET_CONVERSATIONS: (state, payload) => {
            state.conversations = payload
        },
        SET_MESSAGES: (state, payload) => {
            state.conversations[payload.id].messages = payload.data
        },
        SET_LOADING_MESSAGES: (state, payload) => {
            state.loadingMessages = payload
        },
        NEW_MESSAGE: (state, payload) => {
            state.conversations[payload.id].messages.push(payload)
        },
        SET_CONVERSATION_TO_READ: (state, payload) => {
            // NOTE: unread is the 'count'
            // NOTE: payload will be the conv_id
            state.conversations[payload].count = 0
        },
        SET_MESSAGE_AS_LAST: (state, payload) => {
            let conversation = state.conversations[payload.id]
            conversation.message = payload.message
            conversation.date = 'Just now'
            // TODO: inc the count of messages
            if (conversation.count >= 0 && conversation.count < 10 && payload.inc) {
                conversation.count++
            }
        },
        PREPEND_MESSAGES: (state, payload) => {
            state.conversations[payload.id].messages = [...payload.messages, ...state.conversations[payload.id].messages]
        },
        INC_CONVERSATION_OFFSET: (state, payload) => {
            state.conversations[payload.id].offset += 20
        }
    },
    actions: {
        GET_CONVERSATIONS: async (context, payload) => {
            let url = Routing.generate('messages.conversations')

            let {data} = await axiosInstance.get(url)
            context.commit('SET_CONVERSATIONS', data[0])
        },
        GET_MESSAGES: async (context, payload) => {
            let url = Routing.generate('messages.latestMessages', {'conversation_id': payload})
            let {data} = await axiosInstance.get(url)
            //TODO: set the messages to the conversation and not the messages id
            context.commit('SET_MESSAGES', {data: data[0], id: payload})
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
        },
        GET_PREVIOUS_MESSAGES: async (context, payload) => {
            let url = Routing.generate('api.messages.previous', {
                id: payload.id,
                offset: context.state.conversations[payload.id].offset
            })

            let {data} = await axiosInstance.get(url)
            context.commit('PREPEND_MESSAGES', {messages: data[0], id: payload.id})
            context.commit('INC_CONVERSATION_OFFSET', {id: payload.id})
        }
    }
}