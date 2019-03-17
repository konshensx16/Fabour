<template>
    <div>
        <div class="form-group">
            <input type="text"
                   required="required"
                   class="form-control"
                   v-model="comment.content"
            >
        </div>
        <div class="form-group">
            <button type="submit"
                    class="btn btn-info pull-right btn"
                    @click.prevent="saveChanges()"
            >Save changes
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'editForm',
        props: {
            comment: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                loading: false
            }
        },
        methods: {
            saveChanges () {
                this.loading = true
                this.$store.dispatch('UPDATE_COMMENT', {
                    commentId: this.comment.comment_id,
                    content: this.comment.content
                })
                    .then(status => {
                        if (status === 200) {
                            // close the editing form
                        }
                        this.loading = false
                    })

                // TODO: emit an event to the parent to close the editing form
                this.$emit('hide-form', false)
            }
        },
        mounted() {
        }
    }
</script>