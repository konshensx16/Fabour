import Vue from 'vue'
import Vuex from 'vuex'
import userStore from './userStore'
import commentsModule from './commentModule'

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

const routes = require('../routes.json');

Routing.setRoutingData(routes)
Vue.use(Vuex)


export default new Vuex.Store({
    modules: {
        user: userStore,
        comments: commentsModule
    }
})