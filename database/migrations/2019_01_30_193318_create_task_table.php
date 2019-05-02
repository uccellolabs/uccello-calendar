<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('calendar_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->date('date');
            $table->dateTime('dateTime');

            $table->unsignedInteger('module_id');
            $table->foreign('module_id')
                ->references('id')
                ->on('uccello_modules')
                ->onDelete('cascade')
                ->nullable();
                
            $table->unsignedInteger('entity_id')->nullable();
            $table->boolean('done');

            $table->unsignedInteger('assigned_user_id');
            $table->foreign('assigned_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->timestamps();
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('calendar_tasks');
    }
}
