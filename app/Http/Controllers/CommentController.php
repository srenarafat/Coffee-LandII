<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:255'
        ]);
        $comment = Comment::create($data);
        return response()->json($comment);
    }

    public function update(Request $request, Comment $comment)
    {
        $data = $request->validate([
            'text' => 'required|string|max:255'
        ]);
        $comment->update($data);
        return response()->json($comment);
    }
}