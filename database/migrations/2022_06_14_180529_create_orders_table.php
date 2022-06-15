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
            $table->foreignId('concert_id')->constrained('concerts')->onUpdate('cascade')->onDelete('restrict');
            $table->string('email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropForeign(['concert_id']));

        Schema::dropIfExists('orders');
    }
};
