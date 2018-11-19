import Routing from "../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min";
import axios from "axios/index";
const routes = require('../routes.json');

Routing.setRoutingData(routes)

const axiosInstance = axios.create({
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})

export default {
    state: {
        user: {},
        loadingUser: true
    },
    getters: {
        USER: state => {
            return state.user
        },
        LOADING_USER: state => {
            return state.loadingUser
        }
    },
    actions: {
        GET_USER: async (context, payload) => {
            let url = Routing.generate('api.user.getUser', {id: 9}) // will get admin always, must change
            let { data } = await axiosInstance.get(url)
            context.commit('SET_USER', data)
            context.commit('SET_LOADING_USER', false)
        }
    },
    mutations: {
        SET_USER: (state, payload) => {
            state.user = payload.user
        },
        SET_LOADING_USER: (state, payload) => {
            state.loadingUser = payload
        }
    }
}