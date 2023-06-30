<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\Flagable;

class Transaction extends Model
{
    use Flagable, SoftDeletes;
    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $appends = [
        'charge_successfull', 'transfer_successfull'
    ];
    public const FLAG_CHARGE_SUCCESSFULL           = 1;
    public const FLAG_TRANSFER_SUCCESSFULL           = 2;

    public function getChargeSuccessfullAttribute() {
        return ($this->flags & self::FLAG_CHARGE_SUCCESSFULL) == self::FLAG_CHARGE_SUCCESSFULL;

    }

    public function getTransferSuccessfullAttribute() {
        return ($this->flags & self::FLAG_TRANSFER_SUCCESSFULL) == self::FLAG_TRANSFER_SUCCESSFULL;

    }

    public function coin(){
        return  $this->belongsTo(Coin::class,'coin_id','coin_id');
     }
}
