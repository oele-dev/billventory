<?php

namespace StockTaking;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['number','fname','lname','mobile','email','address'];
}
