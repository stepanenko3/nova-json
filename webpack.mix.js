let mix = require('laravel-mix')

require('./nova.mix')

mix.vue({ version: 3 })
   .setPublicPath('dist')
   .js('resources/js/field.js', 'js')
   .nova('stepanenko3/nova-json')
