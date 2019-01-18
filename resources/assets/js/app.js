import 'bootstrap'; // Mandatory to user $.modal()
import 'fullcalendar';
import 'bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker';

var calendar = $('#calendar').fullCalendar({
    header: {
        left:   'title',
        center: '',
        right:  'month,agendaWeek,agendaDay,today prev,next',
    },
    height: "auto",
    locale: 'fr',
    groupByResource: true,
    editable: true,
    handleWindowResize: true,
    weekends: true, // Hide weekends
    displayEventTime: true, // Display event time
    selectable: true,

    selectHelper: true,
    select: function(start, end, jsEvent) {
        // var title = prompt('Event Title:');
        $('#addEventModal #start_date').val(start.format('DD/MM/YYYY'))
        $('#addEventModal #end_date').val(end.subtract(1, "days").format('DD/MM/YYYY'))
        $('#addEventModal').modal('show')

        $('#addEventModal button.save').on('click', (event) =>{

            let url = laroute.route('uccello.calendar.events.create', { 
                domain: $('meta[name="domain"]').attr('content'), 
                type: $('input[name=calendars]:checked').data('calendar-type')
            })

            $.post(url, {
                _token: $("meta[name='csrf-token']").attr('content'),
                subject: $('#subject').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                location: $('#location').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                calendar: $('input[name=calendars]:checked').val()
            }).done(function(){
                console.log('Réussi');
                $('#calendar').fullCalendar('refetchEvents');
            })
        })
        calendar.fullCalendar('unselect');
    },

    eventSources : [
      '/default/calendar/events',
    ]

});

$(document).ready(function()
{
    $('#start_date, #end_date').bootstrapMaterialDatePicker
    ({
        format: 'DD/MM/YYYY HH:mm',
        lang: 'fr',
        weekStart: 1, 
        cancelText : 'ANNULER',
        nowText: "MAINTENANT",
        nowButton : true,
        switchOnClick : true
    });
});