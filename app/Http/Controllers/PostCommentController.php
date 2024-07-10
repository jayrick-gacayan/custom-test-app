<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class PostCommentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', ['store']),
            new Middleware(['auth:api,can:update'], ['update'])
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post)
    {
        $user = Auth::user();

        $post->comments()->save($user->comments()->make($request->all()));
        return response()->json($post->load(['tags', 'comments', 'user']), 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post, Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post, Comment $comment)
    {
        //
        $comment->update($request->all());


        return response()->json($post->load(['tags', 'comments', 'user']), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, Comment $comment)
    {
        //
    }
}
