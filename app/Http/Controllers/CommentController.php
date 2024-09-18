<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // TODO - REMOVE CONTROLLER / USE METHOD addComment()

    public function store(Request $request)
    {
        $data = $request->all();
        if (auth()->user()) {
            $data['user_id'] = auth()->id();
        }
        $comment = new Comment($data);
        $comment->save();
        return redirect()->back();
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return redirect()->back();
    }

}
