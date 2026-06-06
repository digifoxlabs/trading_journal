<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Symbol extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'exchange',
        'instrument_type',
        'tick_size',
        'lot_size',
    ];

    protected $casts = [
        'tick_size' => 'decimal:4',
        'lot_size' => 'decimal:4',
    ];

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function dailyBiases(): HasMany
    {
        return $this->hasMany(DailyBias::class);
    }

    public static function findOrCreateByName(string $name, array $attributes = []): self
    {
        return self::firstOrCreate(
            ['name' => strtoupper(trim($name))],
            array_merge([
                'exchange' => null,
                'instrument_type' => 'equity',
                'tick_size' => 0.0001,
                'lot_size' => 1,
            ], $attributes)
        );
    }
}
