<?php

namespace StockTaking;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id','total','user_id','user_id'];

    public function customer(){
    	return $this->belongsTo('StockTaking\Customer','customer_id');
    }

    public function user(){
    	return $this->belongsTo('StockTaking\User','user_id');
    }

    public function products()
    {
        return $this->belongsToMany('StockTaking\Product','sale_products', 'sale_id', 'product_id')
                    ->withPivot('qty', 'desc','total')->withTimestamps();

    }
}
