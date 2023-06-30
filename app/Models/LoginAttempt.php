<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = "login_attempts";
    protected $appends = [ "is_access_expired" ];
    protected $hidden = [ "flags" ];
    
    public function getIsAccessExpiredAttribute() {
        return $this->access_expiry <= date('Y-m-d H:i:s');
    }
}
