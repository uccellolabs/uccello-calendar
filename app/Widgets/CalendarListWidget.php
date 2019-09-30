<?php

namespace Uccello\Calendar\Widgets;

use Arrilot\Widgets\AbstractWidget;
use App\User;

class CalendarListWidget extends AbstractWidget
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
        $domain = ucdomain($this->config['domain']);

        $users = User::orderBy('name')->get();

        return view('calendar::widgets.calendar_list_widget', [
            'recordId' => request('id'),
            'config' => $this->config,
            'users' => $users,
        ]);
    }
}
