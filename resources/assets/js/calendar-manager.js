export class CalendarManager {
    constructor() {
        this.initCheckboxListener()
    }

    initCheckboxListener() {
        const domainSlug = $('meta[name="domain"]').attr('content')

        $("input[type='checkbox'].calendar-toggle").on('click', (event) => {
            let element = event.currentTarget
            let url = laroute.route('uccello.calendar.toggle', { 
                domain: domainSlug, 
                accountId: $(element).data('accountid'), 
                id: escape($(element).data('calendarid'))
            })

            console.log($(element).data('calendarid'))

            $.get(url, {
                _token: $("meta[name='csrf-token']").attr('content'),
                src_module: 'calendar',
            }).fail((error) => {
                swal('Error', null, 'error')
            })
        })        
    }
}


new CalendarManager();