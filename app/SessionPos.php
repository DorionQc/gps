<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SessionPos extends Model
{
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sessionId', 'lat', 'lng', 'moment',
    ];
}
