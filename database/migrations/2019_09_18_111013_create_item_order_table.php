<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateItemOrderTable extends Migration {

	public function up()
	{
		Schema::create('item-order', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->integer('order_id')->unsigned();
			$table->integer('item_id')->unsigned();
			$table->integer('quantity');
			$table->decimal('price');
			$table->text('sp_note');
		});
	}

	public function down()
	{
		Schema::drop('item-order');
	}
}