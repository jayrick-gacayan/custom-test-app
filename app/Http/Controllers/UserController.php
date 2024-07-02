<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $validate = $request->validate([
        //     'first_name' => 'required|max:255',
        //     'last_name' => 'required|max:255',
        // ]);

        $user = User::create($request->all());

        return response()->json([
            'message' => 'Successfully registered user.',
            'user' => $user
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function verify(string $id)
    {
        $user = User::findOrFail($id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json([
            'message' => 'Successfully verified email address',
            'user' => $user
        ], 200);
    }

    public function forgot_password(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        try {
            $response = Password::sendResetLink($request->only('email'));

            switch ($response) {
                case Password::RESET_LINK_SENT:
                    return response()->json([
                        "message" => trans($response),
                        "user" => $user
                    ], 200);
                case Password::INVALID_USER:
                    return response()->json([
                        "message" => trans($response),
                    ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], 400);
        }
    }


    public function reset_password(Request $request)
    {
        try {
            $exceptEmail = $request->except('email');
            $status = Password::reset(
                Arr::set($exceptEmail, 'email', Crypt::decryptString(($request->email))),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            switch ($status) {
                case Password::INVALID_TOKEN:
                    return response()->json([
                        'message' => "Something went wrong. Invalid token"
                    ], 400);
                case Password::INVALID_USER:
                    return response()->json([
                        'message' => "User not found"
                    ], 402);
                case Password::PASSWORD_RESET:
                    return response()->json([
                        'message' => 'Password changed successfully'
                    ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                "message" => "Email must be encrypted"
            ], 403);
        }
    }

    public function login(Request $request)
    {

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = User::where($request->email)->first();;

            $token = $user->createToken('Access Token')->accessToken;

            return response()->json(
                [
                    'message' => 'Successfully login your account',
                    'user' => $user,
                    'token' => $token
                ],
                200
            );
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens->each(function ($token, $key) {
                $token->revoke();
            });

            return response()->json(['message' => 'Successfully logged out']);
        }

        return response()->json(['message' => 'Something went wrong.'], 401);
    }
}
