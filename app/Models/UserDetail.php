<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\Flagable;

class UserDetail extends Model
{
    use Flagable, SoftDeletes;

    protected $table = 'user_details';
    protected $primaryKey = 'user_detail_id';
    protected $keyType = 'string';
    public $incrementing = false;
}
