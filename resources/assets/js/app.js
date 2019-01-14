import 'bootstrap'; // Mandatory to user $.modal()
import 'fullcalendar'

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
    // axisFormat: 'h:mm',
    // allDaySlot: false,
    // defaultView: 'agendaWeek', // Only show week view
    // minTime: '07:30:00', // Start time for the calendar
    // maxTime: '22:00:00', // End time for the calendar

    selectHelper: true,
    select: function(start, end, jsEvent) {
        // var title = prompt('Event Title:');
        $('#addEventModal').modal('show')
        // if (title) {
        //     calendar.fullCalendar('renderEvent',
        //         {
        //             title: title,
        //             start: start,
        //             end: end,
        //             className: 'bg-primary'
        //         },
        //         true // make the event "stick"
        //     );
        // }
        calendar.fullCalendar('unselect');
    },

    // events: function(start, end, callback){
      
    //   var allEvents = [];
    //   var googleEvents = function(start, end, timezone, callback) {
    //       console.log(start);
    //       $.ajax({
    //         url: '/default/calendar/google/events',
    //         dataType: 'json',
    //         data: {
    //           // our hypothetical feed requires UNIX timestamps
    //           start: start.unix(),
    //           end: end.unix()
    //         },
    //         success: function(response) {
    //           callback(response);
    //         }
    //       });
    //   };
    //   var microsoftEvents = function(start, end, timezone, callback) {
    //       $.ajax({
    //         url: '/default/calendar/google/events',
    //         dataType: 'json',
    //         data: {
    //           // our hypothetical feed requires UNIX timestamps
    //           start: start.unix(),
    //           end: end.unix()
    //         },
    //         success: function(response) {
    //           callback(response);
    //         }
    //       });
    //   };
    //   //allEvents.concat(googleEvents).concat(microsoftEvents);
      
    // }

    eventSources : [
      '/default/calendar/google/events',
      '/default/calendar/microsoft/events'
    ]
    
    // header: {
    //     left: 'title',
    //     center: 'agendaDay,agendaWeek,month',
    //     right: 'prev,next today'
    // },
    // editable: true,
    // // firstDay: 1, //  1(Monday) this can be changed to 0(Sunday) for the USA system
    // // selectable: true,
    // defaultView: 'month',

    // axisFormat: 'h:mm',
    // columnFormat: {
    //     month: 'ddd',    // Mon
    //     week: 'ddd d', // Mon 7
    //     day: 'dddd M/d',  // Monday 9/7
    //     agendaDay: 'dddd d'
    // },
    // titleFormat: {
    //     month: 'MMMM yyyy', // September 2009
    //     week: "MMMM yyyy", // September 2009
    //     day: 'MMMM yyyy'                  // Tuesday, Sep 8, 2009
    // },
    // allDaySlot: false,
    // selectHelper: true,
    // select: function(start, end, allDay) {
    //     var title = prompt('Event Title:');
    //     if (title) {
    //         calendar.fullCalendar('renderEvent',
    //             {
    //                 title: title,
    //                 start: start,
    //                 end: end,
    //                 allDay: allDay
    //             },
    //             true // make the event "stick"
    //         );
    //     }
    //     calendar.fullCalendar('unselect');
    // },
    // droppable: true, // this allows things to be dropped onto the calendar !!!
    // drop: function(date, allDay) { // this function is called when something is dropped

    //     // retrieve the dropped element's stored Event Object
    //     var originalEventObject = $(this).data('eventObject');

    //     // we need to copy it, so that multiple events don't have a reference to the same object
    //     var copiedEventObject = $.extend({}, originalEventObject);

    //     // assign it the date that was reported
    //     copiedEventObject.start = date;
    //     copiedEventObject.allDay = allDay;

    //     // render the event on the calendar
    //     // the last `true` argument determines if the event "sticks" (https://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
    //     $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

    //     // is the "remove after drop" checkbox checked?
    //     if ($('#drop-remove').is(':checked')) {
    //         // if so, remove the element from the "Draggable Events" list
    //         $(this).remove();
    //     }

    // },
    // events: function(start, end, timezone, callback) {
    //     $.ajax({
    //         url: '/default/calendar/events',
    //         dataType: 'json',
    //         data: {
    //         // our hypothetical feed requires UNIX timestamps
    //         start: start.unix(),
    //         end: end.unix()
    //         },
    //         success: function(response) {
    //         callback(response);
    //         }
    //     });
    // }

})