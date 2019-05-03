// import 'bootstrap' // Mandatory to user $.modal()
import 'fullcalendar'
import allLocales from 'fullcalendar/dist/locale-all'
import 'materialize-css'
import moment from 'moment'
import 'daterangepicker'

export class Calendar {
    constructor() {
        this.initFullCalendar()
        this.initSaveButtonListener()
        this.initCancelButtonListener()
        this.initDeleteButtonListener()
        this.initAllDayCheckboxListener()
        this.initCalendarToogleListener()
    }

    initFullCalendar() {
        this.calendar = $('#calendar').fullCalendar({
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
            select: (start, end, jsEvent) => {
                this.emptyModal()

                let dateFormat = $('meta[name="calendar-date-format-js"]').attr('content')

                let dateStart = start.format().split('T')
                let dateEnd = end.format().split('T')

                $('#addEventModal #start_date').val(start.format(dateFormat)).change().parent().find('label').addClass('active')


                if (dateStart.length > 1) {
                    $('#addEventModal #start_time').val(dateStart[1])
                } else {
                    $('#addEventModal #all_day').prop('checked', true).change()
                }

                if (dateEnd.length > 1) {
                    $('#addEventModal #end_time').val(dateEnd[1])
                    $('#addEventModal #end_date').val(end.format(dateFormat)).parent().find('label').addClass('active')
                } else {
                    $('#addEventModal #end_date').val(end.subtract(1, "days").format(dateFormat)).parent().find('label').addClass('active')
                }

                $('#addEventModal').modal('open')

                this.calendar.fullCalendar('unselect')
            },
            //Retrieve existing event
            eventClick: (calEvent) => {
                if ($(`a.calendar-name[data-calendar-id="${calEvent.calendarId}"]`).data('readonly')) {
                    M.toast({html: "Cet événement n'est pas modifiable"}) //TODO: Translate
                    return
                }

                this.emptyModal()

                let url = $('meta[name="calendar-retrieve-event-url"]').attr('content')
                $.get(url, {
                    id : calEvent.id,
                    type: calEvent.calendarType,
                    calendarId: calEvent.calendarId,
                    accountId: calEvent.accountId
                }).done((data) => {
                    var json = $.parseJSON(data)

                    //Open popup and fill in fields
                    if (json.id) {
                        $('#addEventModal .delete').removeClass('hide')
                        $('#addEventModal #all_calendars').val(calEvent.calendarId).formSelect()
                    }

                    let startDate = json.start.split(' ')[0]
                    let startTime = json.start.split(' ')[1]
                    let endDate = json.end.split(' ')[0]
                    let endTime = json.end.split(' ')[1]

                    $('#addEventModal #id').val(json.id)
                    $('#addEventModal #start_date').val(startDate).parent().find('label').addClass('active')
                    $('#addEventModal #start_time').val(startTime)
                    $('#addEventModal #end_date').val(endDate).parent().find('label').addClass('active')
                    $('#addEventModal #end_time').val(endTime)
                    $('#addEventModal #subject').val(json.title).parent().find('label').addClass('active')
                    $('#addEventModal #all_day').prop('checked', json.allDay).change().parent().find('label').addClass('active')
                    $('#addEventModal #location').val(json.location).parent().find('label').addClass('active')
                    $('#addEventModal #description').val(json.description).parent().find('label').addClass('active')
                    $('#addEventModal #entityType').val(json.entityType)
                    $('#addEventModal #entityId').val(json.entityId).parent().find('label').addClass('active')
                    $('#addEventModal #'+this.jq(json.calendarId)).prop('checked', true)

                    $('#addEventModal').modal('open')
                })
            },
            eventDrop: (calEvent) => {
                this.updateCalendarEvent(calEvent)
            },
            eventResize: (calEvent) => {
                this.updateCalendarEvent(calEvent)
            },
            viewRender: () => {
                this.showLoader()
            },
            eventAfterAllRender: (view) => {
                this.hideLoader()
            },
            eventSources: [
                $('meta[name="calendar-events-url"]').attr('content'),
            ],
        })
    }

    initSaveButtonListener() {
        // Save event
        $('#addEventModal a.save').on('click', (event) =>{
            this.showLoader()

            let startDate = $('#start_date').val()
            let endDate = $('#end_date').val()

            if(!$('#all_day').is(':checked')) {
                startDate += ' ' + $('#start_time').val().substr(0, 5)
                endDate += ' ' + $('#end_time').val().substr(0, 5)
            }

            let url = $('meta[name="calendar-update-event-url"]').attr('content')
            if($('#addEventModal #id').val()=='') {
                url = $('meta[name="calendar-create-event-url"]').attr('content')
            }

            $.post(url, {
                _token: $("meta[name='csrf-token']").attr('content'),
                id: $('#addEventModal #id').val(),
                type: $('#all_calendars option:selected').data('calendar-type'),
                subject: $('#subject').val(),
                start_date: startDate,
                end_date: endDate,
                location: $('#location').val(),
                description : $('#description').val(),
                entityType: $('#entityType').val(),
                entityId: $('#entityId').val(),
                allDay: $('#all_day').is(':checked'),
                calendarId: $('#all_calendars').val(),
                accountId: $('#all_calendars option:selected').data('account-id'),
            }).done(function(){
                $('#addEventModal').modal('close')
                M.toast({html: "L'événement a été sauvegardé !"}) //TODO: Translate
                $('#calendar').fullCalendar('refetchEvents')
            })
        })
    }

    initCancelButtonListener() {
        //Clear HTML
        $('#addEventModal a.modal-close').on('click', () =>{
            this.emptyModal()
        })
    }

    initDeleteButtonListener() {
        //Delete event
        $('#addEventModal a.delete').on('click', (event) =>{
            this.showLoader()

            let url = $('meta[name="calendar-delete-event-url"]').attr('content')

            $.post(url, {
                _token: $("meta[name='csrf-token']").attr('content'),
                type: $('#all_calendars option:selected').data('calendar-type'),
                id: $('#addEventModal #id').val(),
                calendarId: $('#all_calendars').val(),
                accountId: $('#all_calendars option:selected').data('account-id'),
            }).done(function(){
                $('#addEventModal').modal('close')
                $("#calendar").fullCalendar('removeEvents', $('#addEventModal #id').val())
            })
        })
    }

    initAllDayCheckboxListener() {
        //Update datetime on checkbox checked to remove time
        $('#all_day').change(function() {
            if($(this).is(':checked')) {
                // $('#start_date').val( $('#start_date').val().split(' ')[0])
                // $('#end_date').val( $('#end_date').val().split(' ')[0])

                $('#start_time').hide()
                $('#end_time').hide()
            } else {
                $('#start_time').show()
                $('#end_time').show()
            }
        })
    }

    initCalendarToogleListener() {
        $(".calendar-name").on('click', (event) => {

            event.preventDefault()

            let element = event.currentTarget
            let currentIcon = _.trim($('.is-active', element).text())

            this.showLoader()

            if (currentIcon === 'check_box') {
                $('.is-active', element).text('check_box_outline_blank').change()
            } else {
                $('.is-active', element).text('check_box').change()
            }

            let url = $('meta[name="calendar-toggle-url"]').attr('content')
            $.get(url, {
                id: escape($(element).data('calendar-id')),
                account_id: $(element).data('account-id'),
                src_module: 'calendar',
            })
            .then((response) => {
                $('#calendar').fullCalendar('refetchEvents')
            })
            .fail((error) => {
                this.hideLoader()

                $('.is-active', element).text(currentIcon)

                swal(uctrans.trans('uccello::default.dialog.error.title'), uctrans.trans('uccello::default.dialog.error.message'), 'error')
            })
        })
    }

    updateCalendarEvent(event) {
        let dateFormat = $('meta[name="calendar-date-format-js"]').attr('content')
        let datetimeFormat = $('meta[name="calendar-datetime-format-js"]').attr('content')

        let dateStart = event.allDay ? event.start.format(dateFormat) : event.start.format(datetimeFormat)
        let dateEnd = event.allDay ? event.end.format(dateFormat) : event.end.format(datetimeFormat)

        this.showLoader()

        let url = $('meta[name="calendar-update-event-url"]').attr('content')

        $.post(url, {
            _token: $("meta[name='csrf-token']").attr('content'),
            type: event.calendarType,
            id: event.id,
            start_date: dateStart,
            end_date: dateEnd,
            calendarId: event.calendarId,
            accountId: event.accountId,
            allDay: event.allDay ? 'true' : 'false'
        }).done(() => {
            this.hideLoader()
        })
    }

    showLoader() {
        $('#calendar-loader').css('visibility', 'visible')
    }

    hideLoader() {
        $('#calendar-loader').css('visibility', 'hidden')
    }

    emptyModal() {
        $('#addEventModal #id').val('')
        $('#addEventModal #start_date').val('').parent().find('label').removeClass('active')
        $('#addEventModal #end_date').val('').parent().find('label').removeClass('active')
        $('#addEventModal #start_time').val('')
        $('#addEventModal #end_time').val('')
        $('#addEventModal #subject').val('').parent().find('label').removeClass('active')
        $('#addEventModal #all_day').prop('checked', false).change().parent().find('label').removeClass('active')
        $('#addEventModal #location').val('').parent().find('label').removeClass('active')
        $('#addEventModal #description').val('').parent().find('label').removeClass('active')
        $('#addEventModal #entityType').val('').parent().find('label').removeClass('active')
        $('#addEventModal #entityId').val('').parent().find('label').removeClass('active')
        $('#addEventModal #allCalendars').val('').formSelect().parent().find('label').removeClass('active')
    }

    jq(myid) {
        return myid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" )
    }
}

new Calendar()