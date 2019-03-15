import 'bootstrap'; // Mandatory to user $.modal()
import 'ion-rangeslider';

$(document).ready(function()
{
    $(".js-range-slider").ionRangeSlider({
        min: 0,
        max: 1440,
        skin: "round",
        step: 1,
        grid: true,
        prettify: function(value){
            if(value < 60){
                return value + 'min';
            }else{
                var hours = Math.floor( value / 60);          
                var minutes = value % 60;
                return hours + 'h '+minutes+'min';
            }
        }
    });
    
//     $('#module').on('change', function(e) {
//         let selector = $(this).val();
//         $("#field > option").hide();
//         $("#field > option").filter(function(){return $(this).data('module') == selector}).show();
//   });

});