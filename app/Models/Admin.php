<?php

namespace App\Models;

use App\Concerns\Flagable;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use Flagable;
    protected $table = 'admins';
    protected $primaryKey = 'admin_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $hidden = [
        'id', 'flags', 'password'
    ];
}
