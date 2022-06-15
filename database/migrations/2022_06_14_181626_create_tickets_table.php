<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', fn (Blueprint $table) => $table->dropForeign(['order_id']));

        Schema::dropIfExists('tickets');
    }
};
