<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_items', function (Blueprint $table) {
            $table->integer('vehicleId')->unsigned();
            $table->integer('itemId')->unsigned();
            $table->integer('amount');
            $table->integer('trueAmount');
            $table->primary(['vehicleId', 'itemId']);
            $table->foreign('vehicleId')->references('id')->on('vehicles');
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
        Schema::dropIfExists('vehicle_items');
    }
}
