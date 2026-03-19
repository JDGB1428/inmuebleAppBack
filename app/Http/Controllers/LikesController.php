<?php

namespace App\Http\Controllers;

use App\Http\Requests\LikeRequest;

class LikesController extends Controller
{
    public function store(LikeRequest $request)
    {
        $user = $request->user();
        $propertyId = $request->validated()['property_id'];

        $result = $user->likes()->toggle($propertyId);

        // Si el array 'attached' tiene algo, significa que se agregó el Like
        $isLiked = count($result['attached']) > 0;

        return response()->json([
            'message' => $isLiked ? 'Like agregado' : 'Like eliminado',
            'is_liked' => $isLiked
        ]);
    }
}
