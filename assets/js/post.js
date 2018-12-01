
import tinymce from 'tinymce'
import 'tinymce/themes/modern/theme'
require('../../node_modules/tinymce/skins/lightgray/skin.min.css')
require('../../node_modules/tinymce/skins/lightgray/content.min.css')
// plugins: add all required plugins here
import 'tinymce/plugins/advlist'
import 'tinymce/plugins/autolink'
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


tinymce.init({
    selector: '.editable',
    height: 500,
    width: '100%',
    automatic_uploads:true,
    file_picker_types: 'image',
    file_picker_callbacks: function (cb, value, meta) {
        let input = document.createElement('input')
        input.setAttribute('type', 'file')
        input.setAttribute('accept', 'image/*')
        let reader = new FileReader()

        input.onchange = function () {
            let file = this.files[0]

            reader.onload = function () {
                let id = 'blobid' + (new Date()).getTime()
                let blobCache = tinyMCE.activeEditor.editorUpload.blobCache
                let base64 = reader.result.split(',')[1]
                let blobInfo = blobCache.create(id, file, base64)
                blobCache.add(blobInfo)

                // call the cb
                cb(blobInfo.blobUri(), {title: file.name})
            }
            reader.readAsDataURL(file)
        }
    },
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor textcolor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table contextmenu paste code help wordcount'
    ],
    toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    content_css: [
        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
        '//www.tinymce.com/css/codepen.min.css']
})