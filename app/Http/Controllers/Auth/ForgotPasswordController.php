<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    // use SendsPasswordResetEmails;

    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('guest');
    // }

    public function showForgotPasswordForm()
    {
       return view('auth.passwords.email');
    }

    public function submitForgotPasswordForm(Request $request) {

        $user = User::where('username', $request->username)->first();
        if($user) {
            $password = $this->generatePassword();
            $data = array('username'=>$user->name, 'password'=>$password);
            $this->mail = $user->email;
            Mail::send('mail', $data, function($message) {
                $message->to($this->mail, 'KCK Sales')
                        ->subject('KCK Sales App Password Request.');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            $user->password = bcrypt($password);;
            $user->save();
        } else {
            return view('auth.passwords.email')
                ->with('username', $request->username)
                ->with('error', 'There is no user with this name.');
        }

        return redirect('/login')->with('message', 'Your password reset request submitted. Please check your email.');
    }

    function generatePassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
