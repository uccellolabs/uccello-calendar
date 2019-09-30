<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Uccello\Core\Models\Widget;

class CreateCalendarWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Widget::create([
            'label' => 'widget.calendar_list',
            'type' => 'summary',
            'class' => 'Uccello\Calendar\Widgets\CalendarListWidget',
            'data' => [ "package" => "uccello/calendar" ]
        ]);

        Widget::create([
            'label' => 'widget.calendar',
            'type' => 'summary',
            'class' => 'Uccello\Calendar\Widgets\CalendarWidget',
            'data' => [ "package" => "uccello/calendar" ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Widget::where('label', 'widget.calendar_list')
            ->where('type', 'summary')
            ->where('class', 'Uccello\Calendar\Widgets\CalendarListWidget')
            ->delete();

        Widget::where('label', 'widget.calendar')
            ->where('type', 'summary')
            ->where('class', 'Uccello\Calendar\Widgets\CalendarWidget')
            ->delete();
    }
}
