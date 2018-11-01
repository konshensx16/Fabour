<template>
    <div class="messages-left">
        <div class="slim-pageheader">
            <h6 class="slim-pagetitle">Messages</h6>
            <a href="" class="messages-compose"><i class="icon ion-compose"></i></a>
        </div><!-- slim-pageheader -->
        <div class="messages-list ps ps--theme_default ps--active-y">
            <a class="media"  v-for="user in users" :id="user._id" v-bind:class="{unread : user.isActive}">
                <div class="media-left">
                    <img src="http://via.placeholder.com/500x500" alt="">
                    <span class="square-10 bg-success"></span>
                </div><!-- media-left -->
                <div class="media-body">
                    <div>
                        <h6>{{ user.name }}</h6>
                        <p>{{ user.message }}</p>
                    </div>
                    <div>
                        <span>{{ user.date }}</span>
                    </div>
                </div><!-- media-body -->
            </a><!-- media -->
            <div class="ps__scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps__scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__scrollbar-y-rail" style="top: 0px; height: 687px; right: 0px;"><div class="ps__scrollbar-y" tabindex="0" style="top: 0px; height: 643px;"></div></div></div><!-- messages-list -->
        <div class="messages-left-footer">
            <button class="btn btn-slim btn-uppercase-sm btn-block">Load Older Messages</button>
        </div><!-- messages-left-footer -->
    </div>
</template>

<script>
    export default {
        name: 'messages-left',
        data () {
            return {
                users: [
                    {
                        "_id": "5bd7f7711e54677745876ed5",
                        "guid": "ce048a91-e81e-4859-bdea-fd284c61de9c",
                        "isActive": true,
                        "name": "Frances Glover",
                        "message": "Amet duis reprehenderit ullamco velit in ut anim.",
                        "date": "2016-06-05"
                    },
                    {
                        "_id": "5bd7f771df62538087062494",
                        "guid": "7bcec1c0-2442-4d60-83cf-0887bcc91c3f",
                        "isActive": true,
                        "name": "Veronica Thomas",
                        "message": "Adipisicing voluptate nulla dolor qui.",
                        "date": "2017-03-19"
                    },
                    {
                        "_id": "5bd7f77198899d89c948f2a7",
                        "guid": "460229f2-c781-4c90-9e87-b96579dab8c6",
                        "isActive": false,
                        "name": "Estella Kane",
                        "message": "Consectetur culpa cillum incididunt elit esse exercitation labore ullamco voluptate irure.",
                        "date": "2014-12-20"
                    },
                    {
                        "_id": "5bd7f7717644d7329f52babf",
                        "guid": "05ae3fd7-dfe1-423c-b57c-8b47f8eb9294",
                        "isActive": true,
                        "name": "Eugenia Little",
                        "message": "Incididunt nostrud irure consectetur commodo duis ad ipsum sit do.",
                        "date": "2017-05-03"
                    },
                    {
                        "_id": "5bd7f77197f7512256ba88b3",
                        "guid": "1903d11e-3c96-438a-bc83-c1a48004c5e1",
                        "isActive": false,
                        "name": "Knox Landry",
                        "message": "Ex adipisicing quis cillum esse ad minim et ut Lorem labore cillum.",
                        "date": "2017-03-30"
                    },
                    {
                        "_id": "5bd7f771119b28fd7fcc54cd",
                        "guid": "5f668e08-0560-451a-8dd0-31bee1c8e074",
                        "isActive": false,
                        "name": "Decker Barton",
                        "message": "Quis anim fugiat anim laborum laborum excepteur consequat fugiat non excepteur.",
                        "date": "2016-02-22"
                    },
                    {
                        "_id": "5bd7f771ba064698882e2ff5",
                        "guid": "6760d989-5218-4ba5-a055-db982deecd62",
                        "isActive": true,
                        "name": "Lopez Horne",
                        "message": "Ullamco dolore consectetur sit consectetur ex exercitation do fugiat reprehenderit minim irure aliqua occaecat.",
                        "date": "2014-12-05"
                    },
                    {
                        "_id": "5bd7f771fa053761f2266592",
                        "guid": "96e20adf-243a-423f-8b4f-dad55603fed1",
                        "isActive": true,
                        "name": "Carney Kelley",
                        "message": "Incididunt qui exercitation et dolor excepteur velit ut anim et enim in adipisicing anim.",
                        "date": "2018-03-09"
                    },
                    {
                        "_id": "5bd7f77111cdd3e1afc5bc16",
                        "guid": "b8d0d0f3-7639-404f-b115-446da7a067e7",
                        "isActive": false,
                        "name": "Fanny Torres",
                        "message": "Sunt deserunt cillum sint non Lorem cillum non ullamco voluptate elit elit.",
                        "date": "2015-05-13"
                    },
                    {
                        "_id": "5bd7f7710415b4d2360b3f64",
                        "guid": "31b110a1-9832-4ac1-839d-d70826e31e1c",
                        "isActive": true,
                        "name": "Mandy Cantu",
                        "message": "Do id sunt tempor nostrud enim aliquip nostrud nulla adipisicing occaecat cupidatat sint tempor tempor.",
                        "date": "2018-01-15"
                    },
                    {
                        "_id": "5bd7f771b731dbe12b155f7d",
                        "guid": "5fd36e67-44fa-440b-b063-f41c99c3b065",
                        "isActive": false,
                        "name": "Fran Moss",
                        "message": "Cupidatat eu aliquip aute elit velit deserunt aliquip magna.",
                        "date": "2014-11-12"
                    },
                    {
                        "_id": "5bd7f77126fa05d96620c0af",
                        "guid": "3bb1c234-4a89-4be2-bc37-73fedbbb78a1",
                        "isActive": true,
                        "name": "Reba Walls",
                        "message": "Aliquip sit occaecat sint sunt.",
                        "date": "2014-03-09"
                    },
                    {
                        "_id": "5bd7f771ee71c9ed49e9b69e",
                        "guid": "cc9c855a-a861-46fd-a55b-01d52d8f1a4b",
                        "isActive": false,
                        "name": "Stacey Small",
                        "message": "Eu ad commodo cupidatat quis.",
                        "date": "2018-06-20"
                    },
                    {
                        "_id": "5bd7f77147c823e3777cb025",
                        "guid": "71eadccb-3e2d-478e-b5ae-027cdaa537e7",
                        "isActive": true,
                        "name": "Edna Mendez",
                        "message": "Excepteur culpa laborum exercitation aute ipsum eiusmod consequat ullamco magna proident ea.",
                        "date": "2016-10-13"
                    },
                    {
                        "_id": "5bd7f771a4bb01ef494d650d",
                        "guid": "6509b240-8dfe-446b-89a5-96ec2f6427a6",
                        "isActive": false,
                        "name": "Erin Chavez",
                        "message": "Qui laborum consequat et officia incididunt sint veniam officia do ullamco dolor amet.",
                        "date": "2017-04-29"
                    },
                    {
                        "_id": "5bd7f77160b5db216ddbf7bc",
                        "guid": "06b2acae-d92a-4458-b179-1ab76fd6f8ba",
                        "isActive": false,
                        "name": "Beck Weaver",
                        "message": "Pariatur voluptate in ex mollit cillum in aute enim nulla reprehenderit.",
                        "date": "2015-12-11"
                    }
                ]
            }
        }
    }
</script>