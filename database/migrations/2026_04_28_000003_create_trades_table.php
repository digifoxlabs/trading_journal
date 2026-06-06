<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->string('trade_number')->unique();
            $table->date('trade_date');
            $table->foreignId('symbol_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['long', 'short']);
            $table->string('setup_type')->nullable();
            $table->decimal('entry_price', 18, 4);
            $table->decimal('stop_loss', 18, 4);
            $table->decimal('target_price', 18, 4)->nullable();
            $table->decimal('position_size', 18, 4);
            $table->decimal('entry_fees', 18, 4)->default(0);
            $table->decimal('exit_price', 18, 4)->nullable();
            $table->decimal('exit_fees', 18, 4)->default(0);
            $table->decimal('gross_pnl', 18, 4)->default(0);
            $table->decimal('net_pnl', 18, 4)->default(0);
            $table->decimal('net_pnl_percent', 10, 4)->default(0);
            $table->decimal('r_multiple', 10, 4)->default(0);
            $table->string('result')->default('open');
            $table->text('notes')->nullable();
            $table->boolean('mistake_flag')->default(false);
            $table->boolean('emotion_flag')->default(false);
            $table->timestamps();

            $table->index(['trade_date', 'symbol_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
