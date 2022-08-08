<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('invitations', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('restrict');
			$table->uuid('code');
			$table->string('email')->unique();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::table('invitations', fn (Blueprint $table) => $table->dropForeign(['user_id']));

		Schema::dropIfExists('invitations');
	}
};
