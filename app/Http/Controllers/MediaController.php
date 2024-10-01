<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function getMedia(Request $request)
    {
        $mediaItems = Media::query()
            ->with('post', 'comments')
            ->where('user_id', $request['user.id'])
            ->whereRelation('post', 'parent_id', $request['user.id'])
            ->orderByDesc('id')
            ->get();

        $selectedMediaItem = $mediaItems->get($request['index']);

        if ($selectedMediaItem) {
            return response()->json(['newMedia' => $selectedMediaItem]);
        } else {
            return response()->json(['message' => 'Элемент не найден'], 404);
        }
    }
}
