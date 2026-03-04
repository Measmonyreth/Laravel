<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\clone_card;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    //
    // public function store(Request $request)
    // {
    //     $user = $request->user();
    //     if (! $user) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }
    //     $request->validate([
    //         'cart_id' => 'required|exists:carts,id',
    //        // 'amount' => 'required|numeric',
    //         'payment_method' => 'required|string',
    //     ]);

    //     // get total price of cart
    //     $cartTotal = Cart::where('id', $request->cart_id)->get(['total']);
    //     if ($cartTotal->total > $request->amount) {
    //         return response()->json(['message' => 'Invalid amount'], 400);
    //     }

    //     $user_card = clone_card::where('user_id', $user->id)->get(['amount']);
    //     // calculate payment and store in database
    //     $payment = $user_card->amount - $cartTotal->total;

    //     $payment = Payment::create([
    //         'cart_id' => $request->cart_id,
    //         'user_id' => $user->id,
    //         'amount' => $payment,
    //         'payment_method' => $request->payment_method,
    //         'status' => 'completed',
    //         'payment_date' => now(),
    //     ]);

    //     return response()->json([
    //         'message' => 'Payment processed successfully',
    //         'payment' => $payment,
    //     ], 200);
    // }

    public function store(Request $request)
    {
        // 1. Validate first, before any auth or logic checks
        $validated = $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
            'payment_method' => 'required|string|in:credit_card,debit_card,paypal,cash,master_card,visa',
            'task' => 'nullable|numeric|min:0|max:1',

        ]);

        // 2. Auth guard (middleware is preferred, but kept here as fallback)
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 3. Use first() not get() — get() returns a Collection, not a model
        $cart = Cart::where('id', $validated['cart_id'])
            ->where('user_id', $user->id) // ensure cart belongs to user
            ->first();

        if (! $cart) {
            return response()->json(['message' => 'Cart not found or does not belong to you'], 404);
        }

        // 4. Use first() here too — same bug, get() has no ->amount property
        $userCard = clone_card::where('user_id', $user->id)->first();

        if (! $userCard) {
            return response()->json(['message' => 'No payment card found for this user'], 404);
        }

        // 5. Check sufficient balance
        if ($userCard->amount < $cart->total) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // validate status of card
        if (Payment::where('user_id', $user->id)->where('status', 'completed')->where('cart_id', $cart->id)->exists()) {
            return response()->json(['message' => 'Payment for this cart already exists'], 400);
        }

        // 6. Wrap in a transaction — if payment creation fails, card deduction rolls back
        DB::transaction(function () use ($cart, $userCard, $user, $validated, &$payment) {
            $rate = $validated['task'] ?? 0.3;  // e.g. 0.3 = 30%

            // ✅ Convert rate to percentage amount ONCE
            $taskFee = $cart->total * $rate; // convert to percentage

            $amountCharged = $cart->total + $taskFee;
            // Deduct from user's card
            $userCard->decrement('amount', $amountCharged);

            // Store the payment
            $payment = Payment::create([
                'cart_id' => $cart->id,
                'user_id' => $user->id,
                'amount' => $amountCharged,   // store what was CHARGED, not the remaining balance
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'payment_date' => now(),
            ]);
            // update status of cart to completed
            $cart->status = 'completed';
            $cart->save();
        });

        return response()->json([
            'message' => 'Payment processed successfully',
            'payment' => $payment,
        ], 201); // 201 Created is more correct than 200 for a new resource
    }

    public function getpayment(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payments = Payment::where('user_id', $user->id)->get();

        return response()->json([
            'message' => 'Payments retrieved successfully',
            'payments' => $payments,
        ], 200);
    }
}
