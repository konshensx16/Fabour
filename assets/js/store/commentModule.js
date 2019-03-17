import Routing from "../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min";
import axios from 'axios'

const axiosInstance = axios.create({
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})

export default {
    state: {
        comments: [],
        total: 0,
        offset: 0
    },
    mutations: {
        APPEND_COMMENTS: (state, payload) => {
            state.comments = [...state.comments, ...payload]
        },
        SET_TOTAL: (state, payload) => {
            state.total = payload
        },
        INCREMENT_OFFSET: (state, payload) => {
            state.offset += payload
        },
        REMOVE_COMMENT: (state, {commentId}) => {
            let comments = state.comments

            let rs = _.filter(comments, comment => {
                return comment.comment_id != commentId
            })

            state.comments = [...rs]
        }
    },
    getters: {
        COMMENTS: state => {
            return state.comments
        },
        TOTAL: state => {
            return state.total
        }
    },
    actions: {
        GET_COMMENTS: async ({commit}, { postId }) => {
            let url = Routing.generate('api.comment.getCommentsForPost', {uuid: postId})
            let {data} = await axiosInstance.get(
                url
            )
            // use mutations
            commit('APPEND_COMMENTS', data.comments)
            commit('SET_TOTAL', data.total)

            // this.comments = data.comments
            // this.total = data.total
        },
        GET_MORE_COMMENTS: async ({commit, state}, {postId}) => {
            // todo get more comments
            let {data} = await axiosInstance.get(Routing.generate('api.comment.getMoreCommentsForPost', {
                offset: state.offset,
                uuid: postId
            }))
            // use a mutation
            commit('APPEND_COMMENTS', data)
            // state.comments = [...state.comments, ...data]
            commit('INCREMENT_OFFSET', data.length)
        },
        DELETE_COMMENT: async ({commit}, {commentId}) => {
            let url = Routing.generate('api.comment.delete', {'id': commentId})
            let { status } = await axios.delete(url)
            if (status === 200 || status === 204) {
                commit('REMOVE_COMMENT', {
                    commentId
                })
            }
        }
    }
}