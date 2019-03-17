import Routing from "../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min";
import axios from "axios/index"
import _ from 'lodash'
const routes = require('../routes.json')


Routing.setRoutingData(routes)

const axiosInstance = axios.create({
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})

export default {
    state: {
        user: {},
        loadingUser: true,
        currentUser: {}
    },
    getters: {
        USER: state => {
            return state.user
        },
        LOADING_USER: state => {
            return state.loadingUser
        },
        CURRENT_USER: state => {
            return state.currentUser
        }
    },
    mutations: {
        SET_USER: (state, payload) => {
            state.user = payload.user
        },
        SET_LOADING_USER: (state, payload) => {
            state.loadingUser = payload
        },
        SET_CURRENT_USER: (state, payload) => {
            state.currentUser = payload.user
        }
    },
    actions: {
        GET_USER: async (context, payload) => {
            let url = Routing.generate('api.user.getUser', {id: payload}) // will get admin always, must change
            let { data } = await axiosInstance.get(url)
            context.commit('SET_USER', data)
            context.commit('SET_LOADING_USER', false)
        },
        GET_CURRENT_USER: async (context, payload) => {
            if (_.isEmpty(context.state.currentUser))
            {
                let url = Routing.generate('api.user.currentUser') // will get admin always, must change
                let { data } = await axiosInstance.get(url)
                context.commit('SET_CURRENT_USER', data)
            }
        }
    }
}