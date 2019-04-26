// import 'bootstrap'; // Mandatory to user $.modal()
import 'fullcalendar';
import allLocales from 'fullcalendar/dist/locale-all';
import 'materialize-css'
// import 'bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker';

// import 'bootstrap-notify';

// import { Notify } from './notify'

function jq( myid ) {

    return myid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" );

}

var calendar = $('#calendar').fullCalendar({
    header: {
        left:   'title',
        center: '',
        right:  'month,agendaWeek,agendaDay,today prev,next',
    },
    height: $(document).height() - 150,
    locales: allLocales,
    locale: $('html').attr('lang'),
    timeFormat: 'H:mm',
    groupByResource: true,
    editable: true,
    handleWindowResize: true,
    weekends: true, // Hide weekends
    displayEventTime: true, // Display event time
    selectable: true,
    selectHelper: true,
    //New event creation
    select: function(start, end, jsEvent) {
        $("#addEventModal button.save").html('Enregistrer l\'événement');
        $("#addEventModal input[name=calendars]:not([readonly])").removeAttr("disabled");


        $('#addEventModal #start_date').val(start.format('DD/MM/YYYY')).parent().find('label').addClass('active')
        $('#addEventModal #end_date').val(end.subtract(1, "days").format('DD/MM/YYYY')).parent().find('label').addClass('active')
        $('#addEventModal').modal('open')
        calendar.fullCalendar('unselect');
    },
    //Retrieve existing event
    eventClick: function(calEvent){

        let url = $('meta[name="calendar-retrieve-event-url"]').attr('content')

        $.get(url, {
            id : calEvent.id,
            type: calEvent.calendarType,
            calendarId: calEvent.calendarId,
            accountId: calEvent.accountId
        }).done(function(data){

            var json = $.parseJSON(data);

            console.log(json)
            //Open popup and fill in fields
            // $("#addEventModal button.save").html('Mettre à jour l\'événement');
            // $("#addEventModal input[name=calendars]:not([readonly])").attr("disabled", true);

            $('#addEventModal #id').val(json.id)
            $('#addEventModal #start_date').val(json.start).parent().find('label').addClass('active')
            $('#addEventModal #end_date').val(json.end).parent().find('label').addClass('active')
            $('#addEventModal #subject').val(json.title).parent().find('label').addClass('active')
            $('#addEventModal #all_day').prop('checked', json.allDay).parent().find('label').addClass('active')
            $('#addEventModal #location').val(json.location).parent().find('label').addClass('active')
            $('#addEventModal #description').val(json.description).parent().find('label').addClass('active')
            $('#addEventModal #entityType').val(json.entityType)
            $('#addEventModal #entityId').val(json.entityId).parent().find('label').addClass('active')
            $('#addEventModal #'+jq(json.calendarId)).prop('checked', true)

            $('#addEventModal').modal('open');
        })
    },

    eventSources : [
        $('meta[name="calendar-events-url"]').attr('content'),
    ]

});

// $(document).ready(function()
// {

// //     $('#module').on('change', function(e) {
// //         let selector = $(this).val();
// //         $("#field > option").hide();
// //         $("#field > option").filter(function(){return $(this).data('module') == selector}).show();
// //   });

//     $('#start_date, #end_date').bootstrapMaterialDatePicker
//     ({
//         format: 'DD/MM/YYYY HH:mm',
//         lang: 'fr',
//         weekStart: 1,
//         cancelText : 'ANNULER',
//         nowText: "MAINTENANT",
//         nowButton : true,
//         switchOnClick : true
//     });

//     //Saving event (new ou existing)
//     $('#addEventModal button.save').on('click', (event) =>{

//         if($('#all_day').is(':checked'))
//         {
//             $('#start_date').val( $('#start_date').val().split(' ')[0]);
//             $('#end_date').val( $('#end_date').val().split(' ')[0]);
//         }

//         let url = '';

//         if($('#addEventModal #id').val()=='')
//         {
//             url = laroute.route('uccello.calendar.events.create', {
//                 domain: $('meta[name="domain"]').attr('content'),
//                 type: $('input[name=calendars]:checked').data('calendar-type')
//             })
//         }
//         else
//         {
//             url = laroute.route('uccello.calendar.events.update', {
//                 domain: $('meta[name="domain"]').attr('content'),
//                 type: $('input[name=calendars]:checked').data('calendar-type')
//             })
//         }

//         $.post(url, {
//             _token: $("meta[name='csrf-token']").attr('content'),
//             id: $('#addEventModal #id').val(),
//             subject: $('#subject').val(),
//             start_date: $('#start_date').val(),
//             end_date: $('#end_date').val(),
//             location: $('#location').val(),
//             description : $('#description').val(),
//             entityType: $('#entityType').val(),
//             entityId: $('#entityId').val(),
//             allDay: $('#all_day').is(':checked'),
//             calendarId: $('input[name=calendars]:checked').val(),
//             accountId: $('input[name=calendars]:checked').data('account-id'),
//         }).done(function(){

//             let notify = new Notify();
//             notify.show("L'événement a été sauvegardé !", 'bg-primary', 'bottom', 'center');

//             $('#calendar').fullCalendar('refetchEvents');
//         })
//     });

//     //Clear HTML
//     $('#addEventModal button.cancel').on('click', (event) =>{

//         $('#addEventModal #id').val('')
//         $('#addEventModal #start_date').val('')
//         $('#addEventModal #end_date').val('')
//         $('#addEventModal #subject').val('')
//         $('#addEventModal #all_day').prop('checked', false)
//         $('#addEventModal #location').val('')
//         $('#addEventModal #description').val('')
//         $('#addEventModal #entityType').val('')
//         $('#addEventModal #entityId').val('')
//         $('#addEventModal input[name=calendars]').prop('checked', false)
//     });

//     //Delete event
//     $('#addEventModal button.delete').on('click', (event) =>{

//         let url = laroute.route('uccello.calendar.events.remove', {
//             domain: $('meta[name="domain"]').attr('content'),
//             type: $('input[name=calendars]:checked').data('calendar-type')
//         })

//         $.post(url, {
//             _token: $("meta[name='csrf-token']").attr('content'),
//             id: $('#addEventModal #id').val(),
//             calendarId: $('input[name=calendars]:checked').val(),
//             accountId: $('input[name=calendars]:checked').data('account-id'),
//         }).done(function(){
//             $("#calendar").fullCalendar('removeEvents', $('#addEventModal #id').val());
//         })
//         $('#addEventModal').modal('hide');
//     });

//     //Update datetime on checkbox checked to remove time
//     $('#all_day').change(function() {
//         if($(this).is(':checked'))
//         {
//             $('#start_date').val( $('#start_date').val().split(' ')[0]);
//             $('#end_date').val( $('#end_date').val().split(' ')[0]);
//         }
//     });
// });