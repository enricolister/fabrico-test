<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    const RENT_TYPES = [
        'consultancy',
        'assistance',
        'commercial'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
    ];

    /**
     * 1 to 1 relation to Renter model
     *
     * @return [type]
     */
    public function language()
    {
        return $this->hasOne(Renter::class);
    }
}
