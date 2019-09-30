<?php

namespace Uccello\Calendar\Widgets;

use Arrilot\Widgets\AbstractWidget;

class CalendarWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        return view('calendar::widgets.calendar_widget', [
            'config' => $this->config,
        ]);
    }
}
