<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentaryRequest;
use App\Models\Properties;
use Illuminate\Http\Request;

class CommentaryController extends Controller
{


    public function store(CommentaryRequest $request, Properties $property)
    {
        $commentary = $property->commentary()->create([
            'user_id' => auth()->user()->id,
            'description' => $request->description
        ]);

        $commentary->load('user');

        return response()->json([
            'id' => $commentary->id,
            'name' => $commentary->user->name,
            'description' => $commentary->description,
            'userAvatar' => $comment->user->avatar ?? null,
            'createdAt' => $commentary->created_at,
        ]);
    }

}
