<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->only(['email', 'password']));

        if(!Auth::attempt($request->only(['email', 'password']))) {
            return $this->responseError('', 'email and password do not match', 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->responseSuccess([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }


    /**
     * register user
     */
    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        return $this->responseSuccess([
            'user'  => $user,
            'token' => $user->createToken('API Token ' . $user->name)->plainTextToken,
        ]);
    }


    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        // auth()->user()->tokens()->delete();

        return $this->responseSuccess([
            'message' => 'You have succesfully been logged out'
        ]);
    }
}
