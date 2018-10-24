/*these are being imported in the layout file, doesn compile when requried here for some weird reasons, might debug it later*/
/*
require("./bundles/goswebsocket/js/vendor/autobahn.min.js")
require("./bundles/goswebsocket/js/gos_web_socket_client.js")
*/ 

// this could be turned into something more dynamic, by disabling shared configuration
let webSocket = WS.connect(_WS_URI)
let $connectedClientsCounter = document.getElementById('connectedClientsCounter')


webSocket.on("socket/connect", function (session) {
    let notification = new Notyf({
        delay: 5000
    })

	session.subscribe('comment/channel', function (uri, payload) // payload is the message itself
    {
    	// TODO: Add the notification when recieved
        notification.confirm(payload)

        addNotificationToList(payload);
    })

	session.subscribe('publications/channel', function (uri, payload) // payload is the message itself
    {
        // TODO: Add the notification when recieved
        notification.confirm(payload[0])
    })
    session.subscribe('friendship/channel', function (uri, payload) // payload is an object
    {
        // TODO: Add the notification when recieved
		// TODO: display a small notification for the user, maybe make it click-able
        // client connected
        // everytime an event is published in this channel the function is executed
        notification.confirm(payload['0'])
    })
})

webSocket.on("socket/disconnect", function (error) {
	console.log("Disconnected " + error.reason + " with code " + error.code)	
})

/**
 * Sets the client counter!!
 * @param count
 */
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
let serialize = function (form) {
	let field,
		l,
		s = [];

	if (typeof form == 'object' && form.nodeName == "FORM") {
		let len = form.elements.length;

		for (let i = 0; i < len; i++) {
			field = form.elements[i];
			if (field.name && !field.disabled && field.type != 'button' && field.type != 'file' && field.type != 'hidden' && field.type != 'reset' && field.type != 'submit') {
				if (field.type == 'select-multiple') {
					l = form.elements[i].options.length;

					for (let j = 0; j < l; j++) {
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
/**
 * Create the notification div with the given message
 * TODO: refactor this after to a class
 * @param time
 * @param message
 */
function notify(message, time = 5000)
{
	// create the elements
	if (message === "")
		return false

	let popupBox = document.createElement('div')
	popupBox.classList.add("popup")

	// TODO: maybe trim the message if it's too long!
	let para = document.createElement('p')
	let textNode = document.createTextNode(message) // at this point im sure the message is not null
	para.appendChild(textNode)

	// put everything together
	popupBox.appendChild(para)

	// append the popup to the body
	let body = document.querySelector('body')
	body.appendChild(popupBox)
	// remove the popup after the given time

	setTimeout(function ()
	{
		// TODO: maybe add some fading out before the removing
		popupBox.remove()

	}, time)
}

/**
 * Adds an item to the notifications list
 * @param payload
 */
function addNotificationToList(payload) {

}