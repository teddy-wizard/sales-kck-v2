<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = ['user_id'];
    protected $appends = ['user_name'];

    public function getUserNameAttribute()
    {
        $username = User::find($this->user_id)->name;
        return $username;
    }
}
