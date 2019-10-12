<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration {

	public function up()
	{
		Schema::create('orders', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->integer('payment_method_id')->unsigned();
			$table->decimal('total');
			$table->double('delivery_cost');
			$table->decimal('price');
			$table->text('note');
			$table->string('delivery_address');
			$table->decimal('net');
			$table->decimal('commission');
			$table->integer('resturant_id')->unsigned();
			$table->enum('state', array('pending', 'accepted', 'rejected'));
			$table->integer('client_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('orders');
	}
}