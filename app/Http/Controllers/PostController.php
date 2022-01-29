<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function list()
    {
        $posts = Post::get();
        $data = collect();
        foreach ($posts as $post) {
            $data->add([
                'id'          => $post->id,
                'title'       => $post->title,
                'description' => $post->description,
                'tags'        => $post->tags,
                'like_counts' => $post->likes->count(),
                'created_at'  => $post->created_at,
                'updated_at' => $post->updated_at
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    
    public function toggleReaction(Request $request)
    {
        $request->validate([
            'post_id' => 'required|numeric|exists:posts,id',
            'like'   => 'required|boolean'
        ]);
        
        $post = Post::find($request->post_id);
        if(!$post) {
            return response()->json([
                'status' => 404,
                'message' => 'post data not found'
            ]);
        }
        
        if($post->user_id == auth()->id()) {
            return response()->json([
                'status' => 500,
                'message' => 'You cannot like your post'
            ]);
        }
        
        $like = Like::where('post_id', $request->post_id)->where('user_id', auth()->id())->first();
        if($like && $request->like) {
            return response()->json([
                'status' => 500,
                'message' => 'You already liked this post'
            ]);
        }elseif($like && !$request->like) {
            $like->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'You unlike this post successfully'
            ]);
        }
        
        Like::create([
            'post_id' => $request->post_id,
            'user_id' => auth()->id()
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'You like this post successfully'
        ]);
    }
}
