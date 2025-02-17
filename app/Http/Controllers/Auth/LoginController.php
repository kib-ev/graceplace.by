<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username()
    {
        return 'phone';
    }

    public function login(Request $request)
    {
        $person = Person::whereHas('phones', function (Builder $query) use ($request) {
            $query->where('number', $request->get('phone'));
        })->first();

        if(isset($person->master) && $request->get('password') == 'graceplace' . $person->master->id) {

            User::updateOrCreate([

                'email' => Str::replace(['+'], '', $request->get('phone')). '@graceplace.by',
                'phone' => $request->get('phone'),
            ], [
                'name' => $person->master->full_name,
                'password' => bcrypt($request->get('password'))
            ]);
        }

        $result = Auth::attempt([
            'phone' => $request->phone,
            'password' => $request->password,
        ], $request->has('remember'));

        return $result ? redirect()->to('/') : back();
    }
}
