<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOffersTable extends Migration {

	public function up()
	{
		Schema::create('offers', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->integer('resturant_id')->unsigned();
			$table->string('name');
			$table->string('content');
			$table->decimal('price');
			$table->date('starting_at');
			$table->date('ending_at');
		});
	}

	public function down()
	{
		Schema::drop('offers');
	}
}