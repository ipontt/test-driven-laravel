<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('concerts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('restrict');
			$table->string('title');
			$table->string('subtitle')->nullable();
			$table->dateTime('date');
			$table->integer('ticket_price');
			$table->string('venue');
			$table->string('venue_address');
			$table->string('city');
			$table->string('state');
			$table->string('zip');
			$table->text('additional_information')->nullable();
			$table->dateTime('published_at')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::table('concerts', fn (Blueprint $table) => $table->dropForeign(['user_id']));

		Schema::dropIfExists('concerts');
	}
};
