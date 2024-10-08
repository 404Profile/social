<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store(Request $request)
    {
        $liked = false;

        if (!empty($request->post)) {
            $likedPost = Like::query()
                ->where('user_id', Auth::id())
                ->where('likeable_type', 'post')
                ->where('post_id', $request['post.id'])
                ->first();

            $post = Post::query()->findOrFail($request['post.id']);

            if ($likedPost) {
                $this->unlike($request, $likedPost);
            } else {
                $this->like($request);
                $liked = true;
            }

            return response()->json(['liked' => $post->likes()->count(), 'isUserLikedPost' => $liked]);
        } elseif (!empty($request->media)) {
            $likedMedia = Like::query()
                ->where('user_id', Auth::id())
                ->where('likeable_type', 'media')
                ->where('media_id', $request['media.id'])
                ->first();

            $media = Media::query()->findOrFail($request['media.id']);

            if ($likedMedia) {
                $this->unlike($request, $likedMedia);
            } else {
                $this->like($request);
                $liked = true;
            }

            return response()->json(['liked' => $media->likes()->count(), 'isUserLikedMedia' => $liked]);
        } elseif (!empty($request->comment)) {
            $likedComment = Like::query()
                ->where('user_id', Auth::id())
                ->where('likeable_type', 'comment')
                ->where('comment_id', $request['comment.id'])
                ->first();

            $comment = Comment::query()->findOrFail($request['comment.id']);

            if ($likedComment) {
                $this->unlike($request, $likedComment);
            } else {
                $this->like($request);
                $liked = true;
            }

            return response()->json(['liked' => $comment->likes()->count(), 'isUserLikedComment' => $liked]);
        }
    }

    public function unlike(Request $request, $likedElement)
    {
        $likedElement->delete();
    }

    public function like(Request $request)
    {
        $like = new Like();

        if (!empty($request->post)) {
            $like->user_id = Auth::id();
            $like->like = true;
            $like->likeable_type = 'post';
            $like->post_id = $request['post.id'];
        } elseif (!empty($request->media)) {
            $like->user_id = Auth::id();
            $like->like = true;
            $like->likeable_type = 'media';
            $like->media_id = $request['media.id'];
        } elseif (!empty($request->comment)) {
            $like->user_id = Auth::id();
            $like->like = true;
            $like->likeable_type = 'comment';
            $like->comment_id = $request['comment.id'];
        }

        $like->save();
    }
}
