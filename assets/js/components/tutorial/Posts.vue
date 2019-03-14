<template>
    <div>
        <div v-for="post in posts" :key="post.id">
            <Post :post="post" />
        </div>
        <h3 class="text-center" v-if="loading">Loading posts...</h3>
    </div>
</template>

<script>
    import axios from 'axios'
    import Post from './Post'

    export default {
        components: {Post},
        data () {
            return {
                posts   : [],
                total   : 0,
                offset  : 0,
                loading : true
            }
        },
        async mounted () {
            let { data } = await axios.get('/tutorial/api/getposts')

            this.posts = data.posts
            this.total = data.total
            this.offset = data.posts.length

            this.loading = false

            window.addEventListener('scroll', async (e) => {
                console.log('scrolled ')
                if ((window.innerHeight + window.pageYOffset >= document.body.offsetHeight) && this.offset < this.total) {
                    this.loading  = true
                    let { data } = await axios.get('/tutorial/api/getposts/' + this.offset)
                    console.log('more data')
                    this.posts = [...this.posts, ...data.posts]
                    this.offset += data.posts.length

                    this.loading  = false
                }
            })


        }
    }
</script>