/*these are being imported in the layout file, doesn compile wehn requried here for some weird reasons, might debug it later*/
/*
require("./bundles/goswebsocket/js/vendor/autobahn.min.js")
require("./bundles/goswebsocket/js/gos_web_socket_client.js")
*/

// this could be turned into something more dynamic, by disabling shared configuration
var webSocket = WS.connect(_WS_URI)
var $connectedClientsCounter = document.getElementById('connectedClientsCounter')
var connectedClientsCounter = 0

webSocket.on("socket/connect", function (session) {
	console.log(session.registrations)
	// client connected
	console.log("Succesfuly connected")
	// update the connected clinets counter, what the fck this could coud be improved why am i like dis 
	connectedClientsCounter += 1
	
	$connectedClientsCounter.innerHTML = connectedClientsCounter
	console.log(connectedClientsCounter)
	// everytime an event is published in this channel the function is executed
	session.subscribe('acme/channel', function (uri, payload) {
		console.log("recieved something in the acme channel: " + payload.msg)
	})

	session.publish('acme/channel', 'This is a message')

	// TODO: send the message from the interface
	var messageBox = document.querySelector('#form_message')
	var sendButton = document.querySelector('#form_send')

	sendButton.addEventListener('click', function (event) {
		event.preventDefault()
		session.publish('acme/channel', messageBox.value)
	})

})

webSocket.on("socket/disconnect", function (error) {
	console.log("Succesfuly disconnected " + error.reason + " with code " + error.code)	
})
