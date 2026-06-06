<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPerformance extends Model
{
    protected $table = 'monthly_performance';

    protected $fillable = [
        'month',
        'year',
        'total_trades',
        'winning_trades',
        'losing_trades',
        'net_pnl',
        'avg_r_multiple',
        'win_rate',
        'profit_factor',
        'max_drawdown',
    ];
}
