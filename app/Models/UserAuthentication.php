<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
class UserAuthentication extends Authenticatable implements JWTSubject
{
    use HasFactory , HasApiTokens;

    protected $table = 'user_authentication';

    protected $fillable = [
        'email',
        'password',
        'phone_no',
    ];

    protected $hidden = [
        'password',
    ];

    // Define a relationship with UserDetails
    public function userDetails()
    {
        return $this->hasOne(UserDetails::class, 'user_authentication_id');
    }

    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }    
}