<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyBias extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'daily_biases';

    protected $fillable = [
        'date',
        'symbol_id',
        'bias',
        'htf_trend',
        'key_levels',
        'expected_move',
        'invalidation_level',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'invalidation_level' => 'decimal:4',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class)->withTrashed();
    }
}
