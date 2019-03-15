const mix = require('laravel-mix');
const path = require('path');

var autoload = {
    jquery: [ '$', 'jQuery', 'jquery']
}

mix.autoload(autoload);

mix.setPublicPath('public');

mix.js('./resources/assets/js/app.js', 'public/js')
    .sass('./resources/assets/sass/app.scss', 'public/css')
    .sass('./resources/assets/sass/rangeslider.scss', 'public/css')
mix.js('./resources/assets/js/fr.js', 'public/js')
mix.js('./resources/assets/js/calendar-manager.js', 'public/js')
mix.js('./resources/assets/js/notify.js', 'public/js')
mix.js('./resources/assets/js/config.js', 'public/js')

// mix.sass('resources/assets/sass/app.scss', 'public/css', {
//   implementation: require('node-sass')
// });

mix.version();

// Copy all compiled files into main project (auto publishing)
mix.copyDirectory('public', '../../../public/vendor/uccello/calendar');