<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\Flagable;

class Coin extends Model
{
    use Flagable, SoftDeletes;
    protected $table = 'coins';
    protected $primaryKey = 'coin_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $hidden = [
        'stripe_response'
    ];

    protected $appends = [
        'coin_image_url', 'is_opened'
    ];
    public const FLAG_IS_OPENED           = 1;

    public function given_friend () {
        return $this->hasOne('App\Models\User', 'user_id', 'to');
    }

    public function sender () {
        return $this->hasOne('App\Models\User', 'user_id', 'from');
    }

    public function medias () {
        return $this->hasMany('App\Models\CoinMedia', 'coin_id', 'coin_id');
    }
    
    public function getIsOpenedAttribute() {
        return ($this->flags & self::FLAG_IS_OPENED) == self::FLAG_IS_OPENED;
        
    }
    public function getCoinImageUrlAttribute() {
        if ($this->coin_image) {
            return url('/').'/assets/coins/'.$this->coin_id.'/'.$this->coin_image;
        }
        return null;
    }
}
