<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'username', 'password', 'company_ids'
    ];
    protected $appends = ['role',  'sales_info'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getRoleAttribute()
    {
        $roles = [];
        $userRoles = UserRole::where('user_id', $this->id)->get();
        foreach($userRoles as $userRole) {
            array_push($roles, $userRole->role_id);
        }
        return $roles;
    }

    public function getSalesInfoAttribute()
    {
        $salesInfo = MsSalesPeople::where('userId', $this->id)->first();
        return $salesInfo;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function isAdmin() {
        return $this->role == 1 ? true : false;
    }

}
