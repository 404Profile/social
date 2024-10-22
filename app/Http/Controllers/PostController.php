<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
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
    public function store(Request $request)
    {
        if ($request->hasFile('files')) {
            Validator::make($request->all(), [
                'body' => ['nullable', 'string', 'max:65535'],
                'owner_user_id' => ['required'],
                'files.*' => ['required', 'max:5120', 'mimes:jpg,png,gif,jpeg'],
            ])->validate();
        } else {
            Validator::make($request->all(), [
                'body' => ['required', 'string', 'max:65535'],
                'owner_user_id' => ['required'],
                'files.*' => ['nullable', 'max:5120', 'mimes:jpg,png,gif,jpeg'],
            ])->validate();
        }

        $post = new Post();
        $post->author_user_id = Auth::id();
        $post->owner_user_id = $request['owner_user_id'];
        $post->body = $request['body'];
        $post->save();

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = 'media/'.now()->format('Y').'/'.now()->format('m');
                $file->store($filePath, 'public');

                Media::create([
                    'post_id' => $post->id,
                    'user_id' => $request->user()->id,
                    'filename' => $file->hashName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return Redirect::back()->with('success', 'Публикация была успешно создана');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
