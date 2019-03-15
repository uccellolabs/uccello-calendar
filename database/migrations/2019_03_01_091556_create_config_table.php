<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_configs', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('domain_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('domain_id')
                ->references('id')->on('uccello_domains')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_configs');
    }
}
