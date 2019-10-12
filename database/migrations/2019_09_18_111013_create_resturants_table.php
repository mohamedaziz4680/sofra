<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResturantsTable extends Migration {

	public function up()
	{
		Schema::create('resturants', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('name');
			$table->string('email');
			$table->string('phone');
			$table->integer('neighborhood_id')->unsigned();
			$table->string('password');
			$table->integer('category_id')->unsigned();
			$table->decimal('minmum_order');
			$table->decimal('delivery_fees');
			$table->string('contact_phone');
			$table->string('whatsapp');
			$table->string('image')->nullable();
			$table->string('api_token')->nullable();
			$table->string('pin_code')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('resturants');
	}
}