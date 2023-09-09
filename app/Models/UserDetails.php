<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserDetails extends Authenticatable implements JWTSubject
{
    use HasFactory , HasApiTokens;

    protected $table = 'user_details';

    protected $fillable = [
        'user_authentication_id',
        'name',
        'other_details_column1',
        'other_details_column2',
        'other_details_column3',
    ];

    // Define a relationship with UserAuthentication
    public function userAuthentication()
    {
        return $this->belongsTo(UserAuthentication::class, 'user_authentication_id');
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