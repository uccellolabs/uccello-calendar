// import 'bootstrap' // Mandatory to user $.modal()
import 'fullcalendar'
import allLocales from 'fullcalendar/dist/locale-all'
import 'materialize-css'

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
            height: $(document).height() - 180,
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
                $("#addEventModal button.save").html('Enregistrer l\'événement')
                $("#addEventModal input[name=calendars]:not([readonly])").removeAttr("disabled")


                $('#addEventModal #start_date').val(start.format('DD/MM/YYYY')).parent().find('label').addClass('active')
                $('#addEventModal #end_date').val(end.subtract(1, "days").format('DD/MM/YYYY')).parent().find('label').addClass('active')
                $('#addEventModal').modal('open')
                this.calendar.fullCalendar('unselect')
            },
            //Retrieve existing event
            eventClick: (calEvent) => {
                if ($(`a.calendar-name[data-calendar-id="${calEvent.calendarId}"]`).data('readonly')) {
                    M.toast({html: "Cet événement n'est pas modifiable"}) //TODO: Translate
                    return
                }

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
                        $(`#addEventModal #all_calendars option[data-account-id='${calEvent.accountId}']`).prop('selected', true)
                        $('#addEventModal #all_calendars').select()
                    }

                    $('#addEventModal #id').val(json.id)
                    $('#addEventModal #start_date').val(json.start).parent().find('label').addClass('active')
                    $('#addEventModal #end_date').val(json.end).parent().find('label').addClass('active')
                    $('#addEventModal #subject').val(json.title).parent().find('label').addClass('active')
                    $('#addEventModal #all_day').prop('checked', json.allDay).parent().find('label').addClass('active')
                    $('#addEventModal #location').val(json.location).parent().find('label').addClass('active')
                    $('#addEventModal #description').val(json.description).parent().find('label').addClass('active')
                    $('#addEventModal #entityType').val(json.entityType)
                    $('#addEventModal #entityId').val(json.entityId).parent().find('label').addClass('active')
                    $('#addEventModal #'+this.jq(json.calendarId)).prop('checked', true)

                    $('#addEventModal').modal('open')
                })
            },
            eventSources : [
                $('meta[name="calendar-events-url"]').attr('content'),
            ],
            eventAfterAllRender: (view) => {
                this.hideLoader()
            }
        })
    }

    initSaveButtonListener() {
        // Save event
        $('#addEventModal a.save').on('click', (event) =>{
            this.showLoader()

            if($('#all_day').is(':checked'))
            {
                $('#start_date').val( $('#start_date').val().split(' ')[0])
                $('#end_date').val( $('#end_date').val().split(' ')[0])
            }

            let url = ''

            if($('#addEventModal #id').val()=='')
            {
                url = $('meta[name="calendar-create-event-url"]').attr('content')
            }
            else
            {
                url = $('meta[name="calendar-update-event-url"]').attr('content')
            }

            $.post(url, {
                _token: $("meta[name='csrf-token']").attr('content'),
                id: $('#addEventModal #id').val(),
                type: $('#all_calendars option:selected').data('calendar-type'),
                subject: $('#subject').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
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
            $('#addEventModal #id').val('')
            $('#addEventModal #start_date').val('')
            $('#addEventModal #end_date').val('')
            $('#addEventModal #subject').val('')
            $('#addEventModal #all_day').prop('checked', false)
            $('#addEventModal #location').val('')
            $('#addEventModal #description').val('')
            $('#addEventModal #entityType').val('')
            $('#addEventModal #entityId').val('')
            $('#addEventModal input[name=calendars]').prop('checked', false)
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
            if($(this).is(':checked'))
            {
                $('#start_date').val( $('#start_date').val().split(' ')[0])
                $('#end_date').val( $('#end_date').val().split(' ')[0])
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

    showLoader() {
        $('#calendar-loader').css('visibility', 'visible')
    }

    hideLoader() {
        $('#calendar-loader').css('visibility', 'hidden')
    }

    jq(myid) {
        return myid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" )
    }
}

new Calendar()