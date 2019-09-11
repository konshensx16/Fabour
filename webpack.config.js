let Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    // TODO: these need a better approach of requiring
    .addStyleEntry('skins/lightgray/skin.min', './node_modules/tinymce/skins/lightgray/skin.min.css')
    .addStyleEntry('skins/lightgray/content.min', './node_modules/tinymce/skins/lightgray/content.min.css')
    .addStyleEntry('appCss', './assets/css/app.css')
    .addEntry('app', './assets/js/app.js')
    // .addStyleEntry('app', './assets/css/app.css')
    .addEntry('conversation', './assets/js/conversation.js')
    .addEntry('posts', './assets/js/posts.js')
    .addEntry('comments', './assets/js/comments.js')
    .addEntry('post', './assets/js/post.js')
    .addEntry('profile', './assets/js/profile.js')

    .addLoader({
        test:  /\.(mp3)$/,
        loader: 'file-loader',
    })

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .enableVueLoader()
    

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
