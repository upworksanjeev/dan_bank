<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\Flagable;

class CoinList extends Model
{
    use Flagable;
    protected $table = 'coin_listings';
    protected $primaryKey = 'coin_listing_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = [
        'active', 'image_url'
    ];
    public const FLAG_ACTIVE = 1;

    public function getActiveAttribute() {
        return ($this->flags & self::FLAG_ACTIVE) == self::FLAG_ACTIVE;

    }
    public function categories () {
        return $this->hasMany('App\Models\CoinCategory', 'coin_listing_id', 'coin_listing_id');
    }
    public function coin_listings () {
        return $this->hasMany('App\Models\CoinList', 'coin_listing_id', 'coin_listing_id');
    }
    public function getImageUrlAttribute() {
        if ($this->image) {
            return url('/').'/assets/coin-lists/'.$this->coin_listing_id.'/'.$this->image;
        }
        return null;
    }
}