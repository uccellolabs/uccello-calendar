import {Datatable} from 'uccello-datatable'

export class CalendarList {
    constructor() {
        this.initDatatable()
    }

    initDatatable() {
        this.datatable = new Datatable()
        this.datatable.init($('table#calendar-list-widget'))

        this.initialContentUrl = $('table#calendar-list-widget').data('content-url')
        this.userId = 'me'
        this.dateStart = ''
        this.dateEnd = ''

        this.datatable.makeQuery()

        $('#calendar-user-id').on('change', (ev) => {
            this.userId = $(ev.currentTarget).val()
            this.refreshDatatable()
        })

        $('#calendar-period').on('change', (ev) => {
            var value = $(ev.currentTarget).val()
            switch(value) {
                case 'today':
                    this.dateStart = this.dateEnd = moment().format('YYYY-MM-DD')
                break

                case 'month':
                    this.dateStart = moment().startOf('month').format('YYYY-MM-DD')
                    this.dateEnd = moment().endOf('month').format('YYYY-MM-DD')
                break

                case 'week':
                    this.dateStart = moment().lang($('html').attr('lang')).startOf('week').format('YYYY-MM-DD')
                    this.dateEnd = moment().lang($('html').attr('lang')).endOf('week').format('YYYY-MM-DD')
                break

                case 'quarter':
                    this.dateStart = moment().lang($('html').attr('lang')).startOf('quarter').format('YYYY-MM-DD')
                    this.dateEnd = moment().lang($('html').attr('lang')).endOf('quarter').format('YYYY-MM-DD')
                break

                default:
                    this.dateStart = ''
                    this.dateEnd = ''
                break
            }

            this.refreshDatatable()
        })
    }

    refreshDatatable() {
        let newUrl = `${this.initialContentUrl}&start=${this.dateStart}&end=${this.dateEnd}&user_id=${this.userId}`
        console.log(newUrl)
        $('table#calendar-list-widget').attr('data-content-url', newUrl)
        this.datatable.makeQuery()
    }
}

new CalendarList()