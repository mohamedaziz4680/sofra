<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateItemsTable extends Migration {

	public function up()
	{
		Schema::create('items', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('image')->nullable();
			$table->string('name');
			$table->integer('resturant_id')->unsigned();
			$table->string('description');
			$table->decimal('price');
			$table->decimal('price_in_offer');
			$table->time('time_to_ready');
		});
	}

	public function down()
	{
		Schema::drop('items');
	}
}