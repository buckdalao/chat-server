<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\StoreResourceFailedException($validator->messages()->first());
        }

        event(new Registered($user = $this->create($request->all())));

        return $this->success();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:12'],
            'email'    => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['required', 'string', 'min:8', 'unique:users'],
        ], [
            'email.unique' => __('auth.email_registered'),
            'phone.unique' => __('auth.phone_has_been_registered'),
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'mb_prefix' => $data['mb_prefix'],
            'phone'     => $data['phone'],
            'photo'     => 'storage/photos/photo.jpg' // default photo
        ]);
        User::query()->whereKey($user->id)->update(['chat_number' => 1000000 + $user->id]);
        if ($data['join_group'] == true || $data['join_group'] == 'true') {
            app('App\Repositories\Chat\ChatGroupUserRepository')->joinGroup($user, 1);
        }
        return $user;
    }
}
