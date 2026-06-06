<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_biases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('symbol_id')->constrained()->cascadeOnDelete();
            $table->enum('bias', ['bullish', 'bearish', 'neutral'])->default('neutral');
            $table->string('htf_trend')->nullable();
            $table->text('key_levels')->nullable();
            $table->string('expected_move')->nullable();
            $table->decimal('invalidation_level', 18, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['date', 'symbol_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_biases');
    }
};
