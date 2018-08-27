/*these are being imported in the layout file, doesn compile when requried here for some weird reasons, might debug it later*/
/*
require("./bundles/goswebsocket/js/vendor/autobahn.min.js")
require("./bundles/goswebsocket/js/gos_web_socket_client.js")
*/

// this could be turned into something more dynamic, by disabling shared configuration
var webSocket = WS.connect(_WS_URI)
var $connectedClientsCounter = document.getElementById('connectedClientsCounter')
var $messagesList = document.getElementById('messagesList')
var connectedClientsCounter = 0

webSocket.on("socket/connect", function (session) {
	// client connected
	
	// everytime an event is published in this channel the function is executed
	session.subscribe('acme/channel', function (uri, payload) {
		setClientCounter(payload.connectedClients)

		// append the message to the messagesList
		var messageListItem = document.createElement('li')
		var messageListItemTextNode = document.createTextNode(payload.msg)
		messageListItem.append(messageListItemTextNode)
		messagesList.append(messageListItem)
	})

	session.publish('acme/channel', 'This is a message')

	// TODO: send the message from the interface
	var messageBox = document.querySelector('#form_message')
	var sendButton = document.querySelector('#form_send')

	sendButton.addEventListener('click', function (event) {
		event.preventDefault()
		var messageValue = messageBox.value
		if (messageValue !== "")
		{
			session.publish("acme/channel", messageValue)
		}
	})

})

webSocket.on("socket/disconnect", function (error) {
	console.log("Disconnected " + error.reason + " with code " + error.code)	
})

function setClientCounter(count)
{
	$connectedClientsCounter.innerHTML = count
}