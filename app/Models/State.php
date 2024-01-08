<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];


    /**
     * Developed By : Pradyumn Dwivedi
     * Created On : 22-mar-2022
     * uses : to to get data of country model in state model 
     */
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * Developed By : Maaz Ansari
     * Created On : 01-Aug-2022
     * uses : to change name to Camel Casing
     */

    // mutators start
    public function setStateNameAttribute($value)
    {
        $this->attributes['state_name'] = ucwords(strtolower($value));
    }

    // mutators end
}
