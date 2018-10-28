import Cropper from 'cropperjs/dist/cropper'
import axios from 'axios'

require('cropperjs/dist/cropper.css')

let cropper;
let preview = document.getElementById('avatar')
let file_input = document.getElementById('user_profile_avatar')
window.previewFile = function () {
    let file = file_input.files[0]
    let reader = new FileReader()

    reader.addEventListener('load', function (event) {
        preview.src = reader.result
    }, false)

    if (file) {
        reader.readAsDataURL(file)
    }
}

preview.addEventListener('load', function (event) {
    // not reached
    cropper = new Cropper(preview, {
        aspectRatio: 1
    })
})

let form = document.getElementById('profile_form')
form.addEventListener('submit', function (event) {
    event.preventDefault()
    log('fuck')
    let targetForm = event.target
    cropper.getCroppedCanvas({
        maxHeight: 1000,
        maxWidth: 1000
    }).toBlob(function (blob) {
        ajaxWithAxios(blob, targetForm)
    })
})

function ajaxWithAxios(blob, form) {
    let data = new FormData(form)
    data.append('file', blob)
    axios({
        method: 'post',
        url: form.getAttribute('action'),
        data: data,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
        .then((response) => {
            console.log(response)
            if (response.data.type === 'success') {
                location.reload()
            }
        })
        .catch((error) => {
            console.error(error)
        })
}

function log(msg) {
    console.log(msg);
}