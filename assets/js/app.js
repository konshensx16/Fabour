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
		let notificationMessage = `${payload.username} commented on your post`
        notification.confirm(notificationMessage)

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
 * TODO: remove this if no longed needed
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
 * TODO: add an indicator when a new notification is added to the list
 * @param payload
 */
function addNotificationToList(payload) {
    // <a href="{{ notification.url }}" class="dropdown-link">
    //     <div class="media">
    //     <img src="{{ asset('assets/img/') ~ notification.avatar }}" alt="">
    //     <div class="media-body">
    //     <p><strong>{{ notification.username }}</strong> {{ notification.action }}</p>
    // <span>{{ notification.date|time_diff }}</span>
    // </div>
    // </div><!-- media -->
    // </a>

    let notificationsList = document.querySelector('#notifications-list')

	// everything is inside the anchor
	let anchor = createElement('a')

	let anchorHref = document.createAttribute('href')
	anchorHref.value = payload.url

	let anchorClass = document.createAttribute('class')
	anchorClass.value = 'dropdown-link'

	anchor.setAttributeNode(anchorClass)
	anchor.setAttributeNode(anchorHref)

	// this holds everything below
	let mediaDiv = createElement('div')
	let mediaDivClassAttribute = document.createAttribute('class')
	mediaDivClassAttribute.value = 'media'
	mediaDiv.setAttributeNode(mediaDivClassAttribute)


	let img = createElement('img')
	// this contains the p
	let mediaBody = createElement('div')
	let mediaBodyClassAttribute = document.createAttribute('class')
	mediaBodyClassAttribute.value = 'media-body'
	mediaBody.setAttributeNode(mediaBodyClassAttribute)

	// this contains the strong and span
	let p = createElement('p')
	let strong = createElement('strong')
	let span = createElement('span')

	let textForStrong = document.createTextNode(payload.username)
	let textForP = document.createTextNode(` ${payload.action}`)
	// set the text to the strong
	strong.appendChild(textForStrong)

	let textForspan	= document.createTextNode('Just now')
	span.appendChild(textForspan)

	p.appendChild(strong)
	p.appendChild(textForP)

	mediaBody.appendChild(p)
	mediaBody.appendChild(span)

    // set an image to the img
    let srcAttribute = document.createAttribute('src')
	srcAttribute.value = payload.avatar

	img.setAttributeNode(srcAttribute)

    mediaDiv.appendChild(img)
	mediaDiv.appendChild(mediaBody)

	anchor.appendChild(mediaDiv)

	// creating the indicator
	let indicator = createElement('span')
	let indicatorClass = document.createAttribute('class')
	indicatorClass.value = 'indicator'

	indicator.setAttributeNode(indicatorClass)

	// append the indicator to the header

	let notificationIcon = document.querySelector('#notification-icon')
	notificationIcon.appendChild(indicator)

	// append everything to the list (don't delete just yet)
    notificationsList.insertAdjacentElement('afterbegin', anchor)
}

function log(msg)
{
	console.log(msg)
}

function createElement(name) {
	return document.createElement(name)
}