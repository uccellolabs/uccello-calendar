import 'fullcalendar'
import allLocales from 'fullcalendar/dist/locale-all'
import 'materialize-css'
import 'daterangepicker'
import 'trumbowyg'
import 'trumbowyg/dist/langs/fr'

export class Calendar {
    constructor() {
        this.modal = $('#addEventModal')

        this.initFullCalendar()
        this.initSaveButtonListener()
        this.initCancelButtonListener()
        this.initDeleteButtonListener()
        this.initAllDayCheckboxListener()
        this.initMeetingCheckboxListener()
        this.initCalendarToogleListener()
        this.initCalendarSwitcherListener()
        this.initDateStartListener()
        this.initDescriptionWysiwyg()
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

                $('#start_date', this.modal).val(start.format(dateFormat)).change().parent().find('label').addClass('active')

                if (dateStart.length > 1) {
                    $('#start_time', this.modal).val(dateStart[1])
                    $('#all_day', this.modal).prop('checked', false).change() // For not checked
                } else {
                    $('#all_day', this.modal).prop('checked', true).change()
                }
                

                if (dateEnd.length > 1) {
                    $('#end_time', this.modal).val(dateEnd[1])
                    $('#end_date', this.modal).val(end.format(dateFormat)).parent().find('label').addClass('active')
                } else {
                    $('#end_date', this.modal).val(end.subtract(1, "days").format(dateFormat)).parent().find('label').addClass('active')
                }

                $('#all_calendars', this.modal).change()
                $(this.modal).modal('open')

                // Dispatch Event
                this.dispatchEvent($(this.modal).attr('id'), 'modal.open')

                this.calendar.fullCalendar('unselect')
            },
            //Retrieve existing event
            eventClick: (calEvent) => {
                if ($(`a.calendar-name[data-calendar-id="${calEvent.calendarId}"]`).data('readonly')) {
                    M.toast({html: "Cet événement n'est pas modifiable"}) //TODO: Translate
                    return
                }

                // Dispatch event
                this.dispatchEvent('calendar', 'event.selected', { calEvent })

                this.emptyModal()
                this.showLoader()

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
                        $('.delete', this.modal).removeClass('hide')
                        $('#all_calendars', this.modal).val(calEvent.calendarId).prop('disabled', true).formSelect().change()
                    }

                    let startDate = json.start.split(' ')[0]
                    let startTime = json.start.split(' ')[1]
                    let endDate = json.end.split(' ')[0]
                    let endTime = json.end.split(' ')[1]

                    if (calEvent.categories) {
                        $(`#category-${calEvent.accountId}`, this.modal).val(calEvent.categories[0]).formSelect().change()
                    }

                    $('#id', this.modal).val(json.id)
                    $('#start_date', this.modal).val(startDate).parent().find('label').addClass('active')
                    $('#start_time', this.modal).val(startTime)
                    $('#end_date', this.modal).val(endDate).parent().find('label').addClass('active')
                    $('#end_time', this.modal).val(endTime)
                    $('#subject', this.modal).val(json.title).parent().find('label').addClass('active')
                    $('#all_day', this.modal).prop('checked', json.allDay).change()
                    $('#location', this.modal).val(json.location).parent().find('label').addClass('active')
                    $('#description', this.modal).trumbowyg('html', json.description)
                    $('#moduleName', this.modal).val(json.moduleName)
                    $('#recordId', this.modal).val(json.recordId)
                    $('#' + this.jq(json.calendarId), this.modal).prop('checked', true)
                    $('#meeting', this.modal).prop('checked', json.attendees.length>0).change()

                    json.attendees.forEach(element => {
                        this.chipInstance.addChip({
                            tag: element.email,
                            image: element.img
                        })
                    });

                    $(this.modal).modal('open')

                    // Dispatch Event
                    this.dispatchEvent($(this.modal).attr('id'), 'modal.open')

                    this.hideLoader()
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
        $('a.save', this.modal).on('click', (event) =>{

            var attendees = [];
            this.chipInstance.chipsData.forEach(element => {
                attendees.push(element.tag)
            });

            this.showLoader()

            let startDate = $('#start_date', this.modal).val()
            let endDate = $('#end_date', this.modal).val()

            if(!$('#all_day', this.modal).is(':checked')) {
                startDate += ' ' + $('#start_time', this.modal).val().substr(0, 5)
                endDate += ' ' + $('#end_time', this.modal).val().substr(0, 5)
            }

            let accountId = $('#all_calendars option:selected', this.modal).data('account-id')

            let url = $('meta[name="calendar-update-event-url"]').attr('content')
            if($('#id', this.modal).val()=='') {
                url = $('meta[name="calendar-create-event-url"]').attr('content')
            }

            $.post(url, {
                _token: $("meta[name='csrf-token']").attr('content'),
                id: $('#id', this.modal).val(),
                type: $('#all_calendars option:selected', this.modal).data('calendar-type'),
                subject: $('#subject', this.modal).val(),
                category: $(`#category-${accountId}`, this.modal).val(),
                start_date: startDate,
                end_date: endDate,
                location: $('#location', this.modal).val(),
                description : $('#description', this.modal).trumbowyg('html'),
                moduleName: $('#moduleName', this.modal).val(),
                recordId: $('#recordId', this.modal).val(),
                allDay: $('#all_day', this.modal).is(':checked'),
                calendarId: $('#all_calendars', this.modal).val(),
                accountId: accountId,
                attendees: attendees
            }).done(() => {
                $(this.modal).modal('close')
                M.toast({html: "L'événement a été sauvegardé !"}) //TODO: Translate
                $('#calendar').fullCalendar('refetchEvents')
            })
        })
    }

    initCancelButtonListener() {
        //Clear HTML
        $('a.modal-close', this.modal).on('click', () =>{
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
                type: $('#all_calendars option:selected', this.modal).data('calendar-type'),
                id: $('#id', this.modal).val(),
                calendarId: $('#all_calendars', this.modal).val(),
                accountId: $('#all_calendars option:selected', this.modal).data('account-id'),
            }).done(() => {
                $(this.modal).modal('close')
                $("#calendar").fullCalendar('removeEvents', $('#addEventModal #id').val())
            })
        })
    }

    initAllDayCheckboxListener() {
        //Update datetime on checkbox checked to remove time
        $('#all_day', this.modal).change((event) => {
            if($(event.currentTarget).is(':checked')) {
                $('#start_time', this.modal).hide()
                $('#end_time', this.modal).hide()
                $('#end_date', this.modal).prop('disabled', false)
            } else {
                $('#start_time', this.modal).show()
                $('#end_time', this.modal).show()
                $('#end_date', this.modal).prop('disabled', true)
                $('#end_date', this.modal).val( $('#start_date', this.modal).val())
            }
        })
    }

    initMeetingCheckboxListener(){
        $('#meeting', this.modal).change((event) => {
            if($(event.currentTarget).is(':checked')) {
                $('.chips', this.modal).show();
            }
            else
            {
                $('.chips', this.modal).hide()
            }
        });
        $('.chips-autocomplete').chips({
            autocompleteOptions: {
              data: {
              },
              limit: Infinity,
              minLength: 1
            }
        });
        this.chipInstance = M.Chips.getInstance($(".chips", this.modal));
    }

    initCalendarToogleListener() {
        $("#calendars-menu .calendar-name").on('click', (event) => {
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

    initCalendarSwitcherListener() {
        $('#all_calendars', this.modal).on('change', (event) => {
            let accountId = $("option:selected", event.currentTarget).attr('data-account-id')
            $('#categories .select-wrapper', this.modal).hide()
            $('#categories').hide()
            $('#location').parent().removeClass('m8')

            // Show category only if it is not empty
            if ($(`#category-${accountId}`, this.modal).find('option').length > 1) {
                $(`#category-${accountId}`, this.modal).parent().show()
                $('#categories').show()
                $('#location').parent().addClass('m8')
            }
        })
    }

    initDescriptionWysiwyg() {
        $.trumbowyg.svgPath = '/vendor/uccello/calendar/images/icons.svg'
        $('#description', this.modal).trumbowyg({
            lang: $('html').attr('lang'),
            //btns: [['bold', 'italic'], ['link']],
            semantic: false,
            resetCss: true,
            height: 200
        })

        // Add custom event listener
        document.getElementById($(this.modal).attr('id')).getElementsByClassName('trumbowyg-editor')[0].addEventListener('update.html', (event) => {
            $('#description', this.modal).trumbowyg('html', event.detail)
        })
    }

    initDateStartListener() {
        $('#start_date', this.modal).on('focusout', (event) => {
            $('#end_date', this.modal).val($(event.currentTarget).val())
        })

        $('#start_time', this.modal).on('change', (event) => {
            let startTime = $(event.currentTarget).val()
            let endTime = moment(startTime, 'HH:mm').add(1, 'hours').format('HH:mm')

            $('#end_time', this.modal).val(endTime)
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
        $('.emptyable', this.modal).val('')
        $('input.emptyable').parent().find('label').removeClass('active')
        $('select.category', this.modal).val('').formSelect()
        $('a.delete').addClass('hide')
        $('#description', this.modal).trumbowyg('empty')
        $('#all_calendars', this.modal).prop('disabled', false).formSelect()
        $('#meeting', this.modal).prop('checked', false)

        for(var i = this.chipInstance.chipsData.length ; i>=0 ; i--)
        {
            this.chipInstance.deleteChip(i);
        }
    }

    jq(myid) {
        return myid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" )
    }

    dispatchEvent(elementId, eventName, data) {
        let event = new CustomEvent(eventName, { detail: data })
        document.getElementById(elementId).dispatchEvent(event)
    }
}

new Calendar()