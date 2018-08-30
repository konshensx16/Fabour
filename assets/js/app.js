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
// get the messages form
var messageForm = document.getElementById('messages_form')

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

	// listen when form submits
	messageForm.addEventListener('submit', function (event)
	{	
		event.preventDefault()
		var formData = new FormData(this)
		// console.log(JSON.stringify(formData)) # this code returned an empty object
		var serilaizedData = JSON.stringify(formToJSON(this.elements))
		console.log(serilaizedData)
		// serialize the form, or just use a FormData
		// so far the formData doesn't seem to be working, the back-end param is empty 
		// maybe i dont need to serialize the form, but what do i need to do
		// time for some battlefield :)
		session.call("messagesManager/store_message", {'form': serilaizedData})
			// handle success & error 
			.then(
				function(response)
				{
					// in case of success
					console.log(response)
				},
				function(error)
				{
					// in case of error
					console.error(error)
				}
			)
	})

	// THIS CODE IS HERE FOR REFERENCES DNT FORGET TO CHANGE IT
	/*
	session.call("sample/add_func", {"term1": 2, "term2": 5}).then(  
        function(result)
        {
            console.log("RPC Valid!", result);
        },
        function(error, desc)
        {
            console.log("RPC Error", error, desc);
        }
    );	
    */
})

webSocket.on("socket/disconnect", function (error) {
	console.log("Disconnected " + error.reason + " with code " + error.code)	
})

function setClientCounter(count)
{
	$connectedClientsCounter.innerHTML = count
}

/**
 * Retrieves input data from a form and returns it as a JSON object.
 * @param  {HTMLFormControlsCollection} elements  the form elements
 * @return {Object}                               form data as an object literal
 */
const formToJSON = elements => [].reduce.call(elements, (data, element) => {
  
  data[element.name] = element.value;
  return data;

}, {});

/**
 * Serializes a given form and returns a string 
 * @param  {[type]} form [description]
 * @return {[type]}      [description]
 */
/*
var serialize = function (form) {
	var field,
		l,
		s = [];

	if (typeof form == 'object' && form.nodeName == "FORM") {
		var len = form.elements.length;

		for (var i = 0; i < len; i++) {
			field = form.elements[i];
			if (field.name && !field.disabled && field.type != 'button' && field.type != 'file' && field.type != 'hidden' && field.type != 'reset' && field.type != 'submit') {
				if (field.type == 'select-multiple') {
					l = form.elements[i].options.length;

					for (var j = 0; j < l; j++) {
						if (field.options[j].selected) {
							s[s.length] = encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[j].value);
						}
					}
				}
				else if ((field.type != 'checkbox' && field.type != 'radio') || field.checked) {
					s[s.length] = encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value);
				}
			}
		}
	}
	return s.join('&').replace(/%20/g, '+');
};
*/
