<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeImage extends Model
{
    public $timestamps = false;

    protected $fillable = ['trade_id', 'timeframe', 'image_path', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }
}
