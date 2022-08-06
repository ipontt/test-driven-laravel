<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('attendee_messages', function (Blueprint $table) {
			$table->id();
			$table->foreignId('concert_id')->constrained('concerts')->onUpdate('cascade')->onDelete('restrict');
			$table->string('subject');
			$table->text('message');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::table('attendee_messages', fn (Blueprint $table) => $table->dropForeign(['concert_id']));

		Schema::dropIfExists('attendee_messages');
	}
};
