<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->body = $request['body'];

        if (!empty($request->post)) {
            $comment->post_id = $request->post['id'];
        } elseif (!empty($request->media)) {
            $comment->media_id = $request->media['id'];
        }

        $comment->save();

        $comment->load('user');

        return response()->json(['comment' => $comment]);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Комментарий успешно удален']);
    }
}
