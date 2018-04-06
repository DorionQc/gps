<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_items', function (Blueprint $table) {
            $table->integer('commandId')->unsigned();
            $table->integer('itemId')->unsigned();
            $table->integer('amount');
            $table->primary(['commandId', 'itemId']);
            $table->foreign('commandId')->references('id')->on('commands');
            $table->foreign('itemId')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('command_items');
    }
}
