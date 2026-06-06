<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbols', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('exchange')->nullable();
            $table->string('instrument_type')->default('equity');
            $table->decimal('tick_size', 12, 4)->default(0.0001);
            $table->decimal('lot_size', 14, 4)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbols');
    }
};
