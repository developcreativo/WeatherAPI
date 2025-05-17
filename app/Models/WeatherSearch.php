<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherSearch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'city',
        'country',
        'temperature',
        'condition',
        'wind_speed',
        'humidity',
        'local_time',
        'raw_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'temperature' => 'float',
        'wind_speed' => 'float',
        'humidity' => 'integer',
        'local_time' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Get the user that performed the search.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
