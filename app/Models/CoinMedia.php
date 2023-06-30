<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinMedia extends Model
{
    protected $table = 'coin_medias';
    protected $primaryKey = 'coin_media_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = [
        'url'
    ];

    public function getUrlAttribute() {
        return url('/').'/assets/coins/'.$this->coin_id.'/'.$this->media_url;
    }
}
