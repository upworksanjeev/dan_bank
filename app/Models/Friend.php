<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\Flagable;

class Friend extends Model
{
    use Flagable;
    protected $table = 'friends';
    protected $primaryKey = 'friend_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = [
        'pending', 'accepted', 'rejected'
    ];

    public const FLAG_PENDING           = 1;
    public const FLAG_ACCEPTED          = 2;
    public const FLAG_REJECTED          = 4;

    public function getPendingAttribute() {
        return ($this->flags & self::FLAG_PENDING) == self::FLAG_PENDING;

    }

    public function getAcceptedAttribute() {
        return ($this->flags & self::FLAG_ACCEPTED) == self::FLAG_ACCEPTED;

    }

    public function getRejectedAttribute() {
        return ($this->flags & self::FLAG_REJECTED) == self::FLAG_REJECTED;

    }

    public function one_friend_profile () {
        return $this->hasOne('App\Models\User', 'user_id', 'user_one')->where('user_id', '!=', request()->user->user_id);
    }

    public function two_friend_profile () {
        return $this->hasOne('App\Models\User', 'user_id', 'user_two')->where('user_id', '!=', request()->user->user_id);
    }
}
