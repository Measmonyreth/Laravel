<?php

namespace App\Http\Controllers;

use App\Models\clone_card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;

class CloneCardController extends Controller
{
    //
    public function store(Request $request)

    {
        $user = $request->user(); // Get the currently authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->validate([
            'card_number' => 'required|string',
            'expiry_date' => 'required|date_format:Y-m-d',
            'amount' => 'required|numeric',
            'cardholder_name' => 'required|string',
            'cvv' => 'required|string'
        ]);
        // hast cvv and number card before store in database
        $existingCard = clone_card::where('card_number', $request->card_number)->first();
        if ($existingCard) {
            return response()->json(['message' => 'Card number already exists'], 409);
        }
        // check cvv
      if(strlen($request->cvv) < 3 || strlen($request->cvv) > 4){
        return response()->json(['message' => 'Invalid CVV'], 400);
      }

        $cloneCard = clone_card::create([
            'user_id' => $user->id,
            'status' => $request->status ?? 'active',
            'type' => $request->type ?? 'credit',
            'card_number' => Hash::make($request->card_number),
            'expiry_date' => $request->expiry_date,
            'amount' => $request->amount,
            'cardholder_name' => $request->cardholder_name,
            'cvv' => Hash::make($request->cvv)
        ]);

        return response()->json([
            'message' => 'Clone card information stored successfully',
            'data' => $cloneCard
        ], 201);
    }

    public function user(Request $request)
    {
        $user = $request->user(); // Get the currently authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->validate([
            'task' => 'nullable|numeric|min:0|max:1',
            'type' => 'nullable|string|in:credit,debit',
            'cvv' => 'required|string',
            'cardholder_name' => 'required|string',
            'expiry_date' => 'nullable|date_format:Y-m-d',
            'card_number' => 'required|string',
        ]);

        $cloneCards = clone_card::where('user_id', $user->id)->get();
        if (!$cloneCards) {
            return response()->json(['message' => 'Clone card information not found'], 404);
        }


        return response()->json([
            'message' => 'Clone card information retrieved successfully',
            'data' => $cloneCards
        ], 200);
    }

    public function update(Request $request)
    {
        $user = $request->user(); // Get the currently authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'amount' => 'required|numeric',
            'cardholder_name' => 'required|string',
            'cvv' => 'required|string'
        ]);

        $cloneCard = clone_card::where('user_id', $user->id)->first();
        if (!$cloneCard) {
            return response()->json(['message' => 'Clone card information not found'], 404);
        }

        $cloneCard->update([
            'card_number' => Hash::make($request->card_number),
            'expiry_date' => $request->expiry_date,
            'amount' => $request->amount,
            'cardholder_name' => $request->cardholder_name,
            'cvv' => Hash::make($request->cvv),
            'status' => $request->status ?? 'active',
            'type' => $request->type ?? 'credit'
        ]);

        return response()->json([
            'message' => 'Clone card information updated successfully',
            'data' => $cloneCard
        ], 200);
    }


}
