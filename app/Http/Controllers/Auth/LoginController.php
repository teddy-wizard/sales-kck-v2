<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\MsSalesPeople;
use App\MsSalesPersonMapping;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/user';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // print(bcrypt('newpassword123!@#'));
        // exit;
        $this->middleware('guest')->except('logout');
    }

    /**
     * Auth with username.
     *
     * @return string
     */
    public function username(){
        return 'username';
    }
}
