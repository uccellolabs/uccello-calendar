const mix = require('laravel-mix')

const autoload = {
   jquery: [ '$', 'jQuery', 'jquery']
}
mix.autoload(autoload)

mix.setPublicPath('public')

mix.js('./resources/assets/js/app.js', 'public/js')
   .sass('./resources/assets/sass/app.scss', 'public/css')
   // .js('./resources/assets/js/config.js', 'public/js')
   .version()

// Copy all compiled files into main project (auto publishing)
   .copy('./resources/assets/images', 'public/images')
   .copyDirectory('public', '../../../public/vendor/uccello/calendar');