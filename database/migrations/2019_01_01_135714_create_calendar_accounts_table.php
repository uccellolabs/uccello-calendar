<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCalendarAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_accounts', function (Blueprint $table) {
            $table->increments('id');

            // Compatibility with Laravel < 5.8
            if (DB::getSchemaBuilder()->getColumnType('users', 'id') === 'bigint') { // Laravel >= 5.8
                $table->unsignedBigInteger('user_id');
            } else { // Laravel < 5.8
                $table->unsignedInteger('user_id');
            }

            $table->string('service_name');
            $table->string('username');
            $table->text('token');
            $table->text('refresh_token')->nullable();
            $table->string('expiration')->nullable();
            $table->text('disabled_calendars')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_accounts');
    }
}
