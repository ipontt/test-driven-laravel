<?php

use App\Models\Concert;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('orders', function (Blueprint $table) {
			$table->id();
			$table->uuid('confirmation_number');
			$table->string('email');
			$table->string('card_last_four', 4);
			$table->unsignedBigInteger('amount');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};
