import 'bootstrap'; // Mandatory to user $.modal()
import 'fullcalendar';
import 'bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker';

function jq( myid ) {
 
    return myid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" );
 
}

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
        calendar.fullCalendar('unselect');
    },

    eventClick: function(calEvent){

        let url = laroute.route('uccello.calendar.events.retrieve', { 
            domain: $('meta[name="domain"]').attr('content'), 
            type: calEvent.calendarType
        })

        $.get(url, {
            _token: $("meta[name='csrf-token']").attr('content'),
            id : calEvent.id,
            calendarId: calEvent.calendarId,
            accountId: calEvent.accountId
        }).done(function(data){
            var json = $.parseJSON(data);
            //Open popup and fill in fields
            $('#addEventModal #id').val(json.id)
            $('#addEventModal #start_date').val(json.start)
            $('#addEventModal #end_date').val(json.end)
            $('#addEventModal #subject').val(json.title)
            $('#addEventModal #all_day').prop('checked', json.allDay)
            $('#addEventModal #location').val(json.location)
            $('#addEventModal #description').val(json.description)
            $('#addEventModal #'+jq(json.calendarId)).prop('checked', true)

            $('#addEventModal').modal('show')
        })
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

    $('#addEventModal button.save').on('click', (event) =>{

        if($('#all_day').is(':checked'))
        {
            $('#start_date').val( $('#start_date').val().split(' ')[0]);
            $('#end_date').val( $('#end_date').val().split(' ')[0]);
        }

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
            description : $('#description').val(),
            allDay: $('#all_day').is(':checked'),
            calendarId: $('input[name=calendars]:checked').val(),
            accountId: $('input[name=calendars]:checked').data('account-id'),
        }).done(function(){
            $('#calendar').fullCalendar('refetchEvents');
        })
    });

    $('#addEventModal button.delete').on('click', (event) =>{

        let url = laroute.route('uccello.calendar.events.remove', { 
            domain: $('meta[name="domain"]').attr('content'), 
            type: $('input[name=calendars]:checked').data('calendar-type')
        })

        $.post(url, {
            _token: $("meta[name='csrf-token']").attr('content'),
            id: $('#addEventModal #id').val(),
            calendarId: $('input[name=calendars]:checked').val(),
            accountId: $('input[name=calendars]:checked').data('account-id'),
        }).done(function(){
            $("#calendar").fullCalendar('removeEvents', $('#addEventModal #id').val());
        })
    });

    $('#all_day').change(function() {
        if($(this).is(':checked'))
        {
            $('#start_date').val( $('#start_date').val().split(' ')[0]);
            $('#end_date').val( $('#end_date').val().split(' ')[0]);
        }
    });
});