let mix = require('laravel-mix');

// Run Mix
mix

    // Webpack config
    .webpackConfig({
        module: {
            rules: [
                {test: /\\.vue$/, loader: 'vue-loader'}
            ]
        }
    })

    // Compile all Sass
    .sass('src/main.scss', 'dist/css')
    .sass('src/fld.scss', 'dist/css')

    // Compile all JavaScript
    .js('src/Configurator.js', 'dist/js')
    .js('src/FieldManipulator.js', 'dist/js')
    .js('src/GroupsDesigner.js', 'dist/js')

    // Compile all Vue components
    .js('src/vue/fld.vue', 'dist/js/vue').vue({version: 3})

    // Compile source maps
    .sourceMaps(true, 'source-map')

    // Disable build notifications
    .disableNotifications()
;
