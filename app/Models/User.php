<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\Flagable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Flagable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $hidden = [
        'id', 'flags', 'password', 'email_verification_token', 'password_verification_token', 'stripe_response', 'customer_response'
    ];

    protected $appends = [
        'logo_image_url', 'banner_image_url', 'active', 'email_verified', 'details_provided', 'bank_account_provided', 'profile_completed_on_stripe'
    ];

    public const FLAG_ACTIVE           = 1;
    public const FLAG_EMAIL_VERIFIED   = 2;
    public const FLAG_DETAILS_PROVIDED   = 4;
    public const FLAG_BANK_ACCOUNT_PROVIDED   = 8;
    public const FLAG_PROFILE_COMPLETED_ON_STRIPE   = 16;

    public function getActiveAttribute() {
        return ($this->flags & self::FLAG_ACTIVE) == self::FLAG_ACTIVE;

    }

    public function getDetailsProvidedAttribute() {
        return ($this->flags & self::FLAG_DETAILS_PROVIDED) == self::FLAG_DETAILS_PROVIDED;

    }

    public function getBankAccountProvidedAttribute() {
        return ($this->flags & self::FLAG_BANK_ACCOUNT_PROVIDED) == self::FLAG_BANK_ACCOUNT_PROVIDED;

    }

    public function getProfileCompletedOnStripeAttribute() {
        return ($this->flags & self::FLAG_PROFILE_COMPLETED_ON_STRIPE) == self::FLAG_PROFILE_COMPLETED_ON_STRIPE;

    }

    public function getEmailVerifiedAttribute() {
        return ($this->flags & self::FLAG_EMAIL_VERIFIED) == self::FLAG_EMAIL_VERIFIED;

    }

    public function getLogoImageUrlAttribute() {
        if ($this->logo_image) {
            return url('/').'/assets/users/'.$this->user_id.'/'.$this->logo_image;
        }
        return null;
    }

    public function getBannerImageUrlAttribute() {
        if ($this->banner_image) {
            return url('/').'/assets/users/'.$this->user_id.'/'.$this->banner_image;
        }
        return null;
    }
}
