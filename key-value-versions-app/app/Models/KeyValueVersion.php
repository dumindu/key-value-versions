<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyValueVersion extends Model
{
    protected $table = 'key_value_versions';

    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'value_hash',
        'created_at',
    ];

    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
