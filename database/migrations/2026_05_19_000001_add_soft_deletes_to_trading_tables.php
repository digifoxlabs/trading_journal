<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('symbols', function (Blueprint $table) {
            $table->dropUnique('symbols_name_unique');
            $table->index('name');
            $table->softDeletes();
        });

        Schema::table('setups', function (Blueprint $table) {
            $table->dropUnique('setups_name_unique');
            $table->index('name');
            $table->softDeletes();
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('daily_biases', function (Blueprint $table) {
            $table->dropUnique('daily_biases_date_symbol_id_unique');
            $table->index(['date', 'symbol_id']);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('daily_biases', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex('daily_biases_date_symbol_id_index');
            $table->unique(['date', 'symbol_id']);
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('setups', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex('setups_name_index');
            $table->unique('name');
        });

        Schema::table('symbols', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex('symbols_name_index');
            $table->unique('name');
        });
    }
};
