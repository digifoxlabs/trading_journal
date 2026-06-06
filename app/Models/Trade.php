<?php

namespace App\Models;

use App\Services\PerformanceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trade extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'trade_number',
        'trade_date',
        'symbol_id',
        'direction',
        'setup_type',
        'entry_price',
        'stop_loss',
        'target_price',
        'position_size',
        'entry_fees',
        'exit_price',
        'exit_fees',
        'gross_pnl',
        'net_pnl',
        'net_pnl_percent',
        'r_multiple',
        'result',
        'notes',
        'mistake_flag',
        'emotion_flag',
    ];

    protected $casts = [
        'trade_date' => 'date',
        'entry_price' => 'decimal:4',
        'stop_loss' => 'decimal:4',
        'target_price' => 'decimal:4',
        'position_size' => 'decimal:4',
        'entry_fees' => 'decimal:4',
        'exit_price' => 'decimal:4',
        'exit_fees' => 'decimal:4',
        'gross_pnl' => 'decimal:4',
        'net_pnl' => 'decimal:4',
        'net_pnl_percent' => 'decimal:4',
        'r_multiple' => 'decimal:4',
        'mistake_flag' => 'boolean',
        'emotion_flag' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Trade $trade) {
            $accountBalance = (float) config('trading.account_balance', 100000);
            $metrics = app(PerformanceService::class)->calculateTradeMetrics($trade->getAttributes(), $accountBalance);
            $trade->fill($metrics);
        });

        static::saved(fn () => app(PerformanceService::class)->syncMonthlyPerformance());
        static::deleted(fn () => app(PerformanceService::class)->syncMonthlyPerformance());
    }

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class)->withTrashed();
    }

    public function images(): HasMany
    {
        return $this->hasMany(TradeImage::class);
    }
}
