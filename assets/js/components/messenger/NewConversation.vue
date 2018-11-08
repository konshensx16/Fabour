<template>
    <div>
        <autocomplete
                :source="url + '/'"
                results-property="friends"
                results-display="username"
                input-class="form-control"
                :results-display="formattedDisplay"
                :resultsFormatter="formatResult"
                @selected="handleRedirect">
        </autocomplete>
    </div>
</template>

<script>
    import _ from 'lodash'
    import Autocomplete from 'vuejs-auto-complete'
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'
    const routes = require('../../routes.json');

    Routing.setRoutingData(routes)

    export default {
        name: 'new-conversation',
        components: {Autocomplete},
        data() {
            return {
                input: '',
                url: Routing.generate("messages.userFriends")
            }
        },
        methods: {
            formatResult: (object) => {
                return _.map(JSON.parse(object.friends), ({username, avatar}) => {
                    return {
                        avatar,
                        username
                    }
                })
            },
            formattedDisplay: ({username, avatar}) => {
                return `
                        <img class="media-img" src="/uploads/avatars/${avatar}" alt="${username}">
                        <div class="media-body">
                          <h6 class="tx-inverse mg-b-10">${username}</h6>
                        </div>
                       `
            },
            handleRedirect: ({selectedObject}) => {
                let url = Routing.generate("messages.newConversation", {'username': selectedObject.username})
                window.location.href = url
            }
        }
    }
</script>