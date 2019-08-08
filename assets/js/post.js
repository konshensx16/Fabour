import tinymce from 'tinymce'
import 'tinymce/themes/modern/theme'

require('../../node_modules/tinymce/skins/lightgray/skin.min.css')
require('../../node_modules/tinymce/skins/lightgray/content.min.css')
// plugins: add all required plugins here
import 'tinymce/plugins/advlist'
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/autosave'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/link'
import 'tinymce/plugins/image'
import 'tinymce/plugins/charmap'
import 'tinymce/plugins/print'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/anchor'
import 'tinymce/plugins/textcolor'
import 'tinymce/plugins/searchreplace'
import 'tinymce/plugins/visualblocks'
import 'tinymce/plugins/code'
import 'tinymce/plugins/fullscreen'
import 'tinymce/plugins/insertdatetime'
import 'tinymce/plugins/media'
import 'tinymce/plugins/table'
import 'tinymce/plugins/contextmenu'
import 'tinymce/plugins/paste'
import 'tinymce/plugins/help'
import 'tinymce/plugins/wordcount'

import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

const routes = require('./routes.json');

Routing.setRoutingData(routes)

let post_id = document.querySelector('#edit_form')

let postAttachmentsUrl = Routing.generate('api.attachment.postimage', {uuid: post_id.dataset.postId})

tinymce.init({
    selector: '.editable',
    height: "480",
    plugins: 'image code autosave',
    auto_save_interval: "5s",
    toolbar: 'undo redo | link image | code',
    // enable title field in the Image dialog
    image_title: true,
    // enable automatic uploads of images represented by blob or data URIs
    automatic_uploads: true,
    images_upload_url: postAttachmentsUrl,
    file_picker_types: 'image',
    // and here's our custom image picker
    file_picker_callback: function (cb, value, meta) {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');

        // Note: In modern browsers input[type="file"] is functional without
        // even adding it to the DOM, but that might not be the case in some older
        // or quirky browsers like IE, so you might want to add it to the DOM
        // just in case, and visually hide it. And do not forget do remove it
        // once you do not need it anymore.

        input.onchange = function () {
            var file = this.files[0];

            var reader = new FileReader();
            reader.onload = function () {
                // Note: Now we need to register the blob in TinyMCEs image blob
                // registry. In the next release this part hopefully won't be
                // necessary, as we are looking to handle it internally.
                var id = 'blobid' + (new Date()).getTime();
                var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                var base64 = reader.result.split(',')[1];
                var blobInfo = blobCache.create(id, file, base64);
                blobCache.add(blobInfo);

                // call the callback and populate the Title field with the file name
                cb(blobInfo.blobUri(), {title: file.name});
            };
            reader.readAsDataURL(file);
        };

        input.click();
    }
});

// listen for key combination
document.body.onkeydown = (e) => {
    let evtobj = window.event ? event : e
    if (evtobj.keyCode == 83 && evtobj.ctrlKey) {
        e.preventDefault()
        // save the post
        // submit the post using an ajax request
    }
}

// bootstrap tags inputs
// $('#post_tags').tagsinput();