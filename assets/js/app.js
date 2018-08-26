/*these are being imported in the layout file, doesn compile wehn requried here for some weird reasons, might debug it later*/
/*
require("./bundles/goswebsocket/js/vendor/autobahn.min.js")
require("./bundles/goswebsocket/js/gos_web_socket_client.js")
*/

// this could be turned into something more dynamic, by disabling shared configuration
var webSocket = WS.connect(_WS_URI)

webSocket.on("socket/connect", (session) => {
	// client connected
	console.log("Succesfuly connected")

	// everytime an event is published in this channel the function is executed
	session.subscribe('acme/channel', (uri, payload) => {
		console.log("recieved something in the acme channel " + payload.msg)
		console.log(payload)
	})

	session.publish('acme/channel', "this is a message")
	session.publish('acme/channel', "this is another message")
	session.publish('acme/channel', "this is a third message")

})

webSocket.on("socket/disconnect", (error) => {
	console.log("Succesfuly disconnected " + error.reason + " with code " + error.code)	
})