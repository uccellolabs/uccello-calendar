<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Uccello\Calendar;

class CreateCalendarTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_types', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('friendly_name');
            $table->text('namespace');
            $table->text('icon');
            $table->timestamps();
        });

        $google = new \Uccello\Calendar\CalendarTypes();
        $google->name = 'google';
        $google->namespace = 'Uccello\\Calendar\\Http\\Controllers\\Google';
        $google->friendly_name = "Google Calendar";
        $google->icon = 'gmail.png';
        $google->save();

        $microsoft = new \Uccello\Calendar\CalendarTypes();
        $microsoft->name = 'microsoft';
        $microsoft->namespace = 'Uccello\\Calendar\\Http\\Controllers\\Microsoft';
        $microsoft->friendly_name = "Microsoft Outlook";
        $microsoft->icon = 'outlook.png';
        $microsoft->save();

        $tasks = new \Uccello\Calendar\CalendarTypes();
        $tasks->name = 'tasks';
        $tasks->namespace = 'Uccello\\Calendar\\Http\\Controllers\\Task';
        $tasks->friendly_name = "Tasks";
        $tasks->icon = '';
        $tasks->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_types');
    }
}
