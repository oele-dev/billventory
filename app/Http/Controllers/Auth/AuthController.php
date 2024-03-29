<?php

namespace StockTaking\Http\Controllers\Auth;

use StockTaking\User;
use Validator;
use StockTaking\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'number'   => 'required|max:12|unique:users',
            'name'     => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'mobile'   => 'required|max:12',
            'password' => 'required|confirmed|min:6',
            'type'     => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'number'    => $data['number'],
            'name'      => $data['name'],
            'email'     => $data['email'],
            'mobile'    => $data['mobile'],
            'password'  => bcrypt($data['password']),
            'type'      => $data['type'],
        ]);
    }
}
