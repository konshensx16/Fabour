<template>
    <div>
        <autocomplete
                :source="url + '/'"
                results-property="friends"
                results-display="username"
                input-class="form-control"
                :results-display="formattedDisplay"
                :resultsFormatter="formatResult">
        </autocomplete>
    </div>
</template>

<script>
    // require('../../../css/app.css')
    // import Typeahead from 'typeahead'
    import Autocomplete from 'vuejs-auto-complete'
    import Bloodhound from 'bloodhound-js'
    import _ from 'lodash'

    const routes = require('../../routes.json');
    import Routing from '../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

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
                        <a href="#">
                            <img class="media-img" src="/uploads/avatars/${avatar}" alt="${username}">
                            <div class="media-body">
                              <h6 class="tx-inverse mg-b-10">${username}</h6>
                            </div>
                        </a>
                       `
            }
        },
        mounted() {
            /*
            let url =

            let engine = new Bloodhound({
                // local: url,
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                datumTokenizer: Bloodhound.tokenizers.whitespace,
                // prefetch: url,
                remote: {
                    url: url,
                    // url: 'messages/friendsList/%QUERY%',
                    wildcard: 'QUERY',
                    filter: (result) => {
                        return _.map(JSON.parse(result.friends), ({username, avatar}) => {
                            return {
                                avatar,
                                username
                            }
                        })
                    }
                }
            })

            let promise = engine.initialize()

            promise.then((result) => {
                engine.search()
            })
            */
            // TODO: change this to use the promise above
            // new Typeahead(this.$refs.query, {
            //     source: ['foo', 'bar', 'baz'], // this works
            //     // NOTE: when assigning result in the search function it's not going to work because at the moment
            //     // the array is empty and the data will be signed after the search (inside the empty array) is
            //     // done, which explain why the list being always empty
            //     // source: engine.ttAdapter(),
            //     display: 'username',
            //     updater: function (item) {
            //
            //     }
            // })
        }
    }
</script>

<style>

    .typeahead,
    .tt-query,
    .tt-hint {
        width: 396px;
        height: 30px;
        padding: 8px 12px;
        font-size: 24px;
        line-height: 30px;
        border: 2px solid #ccc;
        -webkit-border-radius: 8px;
        -moz-border-radius: 8px;
        border-radius: 8px;
        outline: none;
    }

    .typeahead {
        background-color: #fff;
    }

    .typeahead:focus {
        border: 2px solid #0097cf;
    }

    .tt-query {
        -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    }

    .tt-hint {
        color: #999
    }

    .tt-menu {
        width: 422px;
        margin: 12px 0;
        padding: 8px 0;
        background-color: #fff;
        border: 1px solid #ccc;
        border: 1px solid rgba(0, 0, 0, 0.2);
        -webkit-border-radius: 8px;
        -moz-border-radius: 8px;
        border-radius: 8px;
        -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
    }

    .tt-suggestion {
        padding: 3px 20px;
        font-size: 18px;
        line-height: 24px;
    }

    .tt-suggestion:hover {
        cursor: pointer;
        color: #fff;
        background-color: #0097cf;
    }

    .tt-suggestion.tt-cursor {
        color: #fff;
        background-color: #0097cf;

    }

    .tt-suggestion p {
        margin: 0;
    }

    .gist {
        font-size: 14px;
    }

    /* example specific styles */
    /* ----------------------- */

    #custom-templates .empty-message {
        padding: 5px 10px;
        text-align: center;
    }

    #multiple-datasets .league-name {
        margin: 0 20px 5px 20px;
        padding: 3px 0;
        border-bottom: 1px solid #ccc;
    }

    #scrollable-dropdown-menu .tt-menu {
        max-height: 150px;
        overflow-y: auto;
    }

    #rtl-support .tt-menu {
        text-align: right;
    }

</style>