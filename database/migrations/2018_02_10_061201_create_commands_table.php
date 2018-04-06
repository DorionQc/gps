<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('clientId')->unsigned();
            $table->integer('sessionId')->unsigned()->nullable();
            $table->boolean('complete')->default(0);
            $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('clientId')->references('id')->on('clients');
            $table->foreign('sessionId')->references('id')->on('sessions');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commands');
    }
}
