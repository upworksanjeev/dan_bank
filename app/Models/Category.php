<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\Flagable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use Flagable,SoftDeletes;
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = [
        'active',
    ];

    public const FLAG_ACTIVE           = 1;

    public function getActiveAttribute() {
        return ($this->flags & self::FLAG_ACTIVE) == self::FLAG_ACTIVE;

    }

    public function coin_categories () {
        return $this->hasMany('App\Models\CoinCategory', 'category_id', 'category_id');
    }
}
