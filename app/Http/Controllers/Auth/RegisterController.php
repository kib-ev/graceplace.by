<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $this->fillEmail($data);

        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'patronymic' => ['required', 'string', 'max:255'],
            'phone' => ['required',' phone:BY', 'unique:users'], // todo correct validation
//            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
//            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
//        $user = User::create([
//            'name' => implode(' ', [$data['last_name'], $data['first_name']]),
//            'phone' => $data['phone'],
//            'email' => $data['email'],
//            'password' => Hash::make($data['password']),
//        ]);

        $master = (new UserService())->createUserMaster(
            $data['phone'],
            $data['first_name'],
            $data['last_name'],
            $data['patronymic'],
            $data['description'],
            $data['instagram']
        );

        return $master->user;
    }

    /**
     * Generate fake email for user.
     *
     * @param  array  $data
     * @return void
     */
    private function fillEmail(&$data)
    {
        $data['email'] = Str::replace(['+', '-', '(', ')', ' '], '', $data['phone']) . '@' . 'graceplace.by'; // todo domain
    }
}
