<?php

namespace App\Http\Controllers\Customer;

use App\Models\Rating;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RatingController extends Controller
{
    public function ratingPaket(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        $user = $request->user();

        $transaction = Transactions::where('id', $request->transaction_id)
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->first();

        if(!$transaction) {
            return response()->json([
                'message' => 'Unauthorized transaction'
            ], 403);
        }

        $existing = Rating::where('transaction_id', $transaction->id)->first();
        if($existing) {
            return response()->json([
                'message' => 'Anda sudah pernah memberikan rating pada paket ini'
            ], 400);
        }

        $rating = Rating::create([
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'message' => 'Rating saved successfully',
            'data' => $rating
        ]);
    }

    public function showRating($transaction_id)
    {
        $rating = Rating::where('transaction_id', $transaction_id)->first();

        return response()->json([
            'data' => $rating
        ]);
    }
}
