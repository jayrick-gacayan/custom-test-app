<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PostController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', ['update', 'store'])
        ];
    }

    public function index()
    {
        //
        return response()->json(Post::with(['tags', 'user', 'comments'])->get(), 200);
    }

    public function store(Request $request)
    {
        //
        $user = Auth::user();

        $post = $user->posts()->create($request->all());

        return response()->json($post, 200);
    }

    public function show(Post $post)
    {
        return response()->json($post->load(['tags', 'user', 'comments']), 200);
    }


    public function update(Request $request, Post $post)
    {
        $post->update($request->only($post->getFillable()));

        return response()->json($post, 200);
        //
    }


    public function destroy(Post $post)
    {
        //
    }
}
