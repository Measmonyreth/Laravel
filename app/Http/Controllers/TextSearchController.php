<?php

namespace App\Http\Controllers;

use App\Models\TextSearch;
use App\Models\User;
use Illuminate\Http\Request;

class TextSearchController extends Controller
{
    //

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:255',
        ]);

        // $auth = User::find(auth()->id());

        // if(! $auth) {
        //     return response()->json([
        //         'message' => 'Unauthorized',
        //     ], 401);
        // }

        $textSearch = TextSearch::create([
            'text' => $request->text,
            'user_id' => auth()->id(),
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Text search saved successfully',
            //'data' => $textSearch->load('user'),
            'data' => $textSearch
        ], 201);
    }

    public function index(Request $request)
    {
        $textSearches = User::with('textSearches')->get();
        return response()->json([
            'message' => 'Text searches retrieved successfully',
            'data' => [
               // loop user data for each text search
                'text_searches' => $textSearches->map(function ($user) {
                    return [
                        'user' => $user,
                      //  'searches' => $user->textSearches,
                    ];
                })

            ]
        ], 200);
    }

    public function showOfUser(Request $request)
    {
        $textSearches = TextSearch::where('user_id', auth()->id())->get();
        return response()->json([
            'message' => 'Text searches retrieved successfully',
            'data' => $textSearches
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $textSearch = TextSearch::where('user_id', auth()->id())->where('id', $id)->first();
        if(!$textSearch) {
            return response()->json([
                'message' => 'Text search not found',
            ], 404);
        }
        $textSearch->update([
            'status' => 'deleted'
        ]);
        return response()->json([
            'message' => 'Text search deleted successfully',
            'data' => $textSearch
        ], 200);
    }
}
