<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::user()->all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        $user = new User();

        $user->fill(Arr::except($request->only($user->getFillable()), ['profile_image']));

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = Storage::url($file->storeAs('public/front_id', uniqid() . '.' . $file->getClientOriginalExtension()));
            $user->profile_image = env('APP_URL') . $path;
        }
        $user->save();

        return response()->json([
            'message' => 'Successfully registered user.',
            'user' => $user
        ], 200);
    }

    public function show(User $user)
    {


        return response()->json(

            $user->with([
                'posts.tags',
                'posts.comments', function ($query) {
                    $query->whereNull('parent_id');
                },

                'posts.comments.replies'
            ])->get(),

            200
        );
    }

    public function update(Request $request, User $user)
    {
        $user->fill(Arr::except($request->only($user->getFillable()), ['profile_image']));

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = Storage::url($file->storeAs('public/front_id', uniqid() . '.' . $file->getClientOriginalExtension()));
            $user->profile_image = env('APP_URL') . $path;
        }

        $user->save();

        return response()->json($user, 200);
    }

    public function destroy(User $user)
    {
        //
    }

    public function verify(User $user)
    {
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
            $user = User::where($request->email)->first();

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
