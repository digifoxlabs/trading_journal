<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('total_trades')->default(0);
            $table->unsignedInteger('winning_trades')->default(0);
            $table->unsignedInteger('losing_trades')->default(0);
            $table->decimal('net_pnl', 18, 4)->default(0);
            $table->decimal('avg_r_multiple', 10, 4)->default(0);
            $table->decimal('win_rate', 8, 4)->default(0);
            $table->decimal('profit_factor', 12, 4)->default(0);
            $table->decimal('max_drawdown', 18, 4)->default(0);
            $table->timestamps();

            $table->unique(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_performance');
    }
};
