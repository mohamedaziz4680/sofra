<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientsTable extends Migration {

	public function up()
	{
		Schema::create('clients', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('name');
			$table->string('email')->unique();
			$table->string('phone');
			$table->integer('neighborhood_id')->unsigned();
			$table->string('password');
			$table->string('api_token', 60)->unique()->nullable();
			$table->integer('pin_code')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('clients');
	}
}