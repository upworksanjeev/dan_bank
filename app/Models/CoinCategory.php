<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinCategory extends Model
{
    protected $table = 'coin_categories';
    protected $primaryKey = 'coin_category_id';
    protected $keyType = 'string';
    public $incrementing = false;

    public function coin_obj () {
        return $this->hasOne('App\Models\CoinList', 'coin_listing_id', 'coin_listing_id');
    }
    public function category () {
        return $this->hasOne('App\Models\Category', 'category_id', 'category_id');
    }
}
