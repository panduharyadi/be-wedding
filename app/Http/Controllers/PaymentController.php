<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Paket;
use App\Models\Transactions;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function getSnapToken(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|integer|exists:pakets,pk_paket_id',
            'price' => 'required|numeric|min:1000',
            'package_name' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $paket = Paket::where('pk_paket_id', $request->paket_id)->firstOrFail();

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = 'ORDER-' . uniqid();

        $transaction = Transactions::create([
            'order_id' => $orderId,
            'user_id' => $user->id,
            'paket_id' => $paket->pk_paket_id,
            'nama_paket' => $request->package_name,
            'total' => $request->price,
            'tanggal' => now(),
            'status' => 'success'
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email
            ]
        ];

        $snapToken = Snap::getSnapToken($payload);

        return response()->json([
            'token' => $snapToken,
            'order_id' => $orderId,
        ]);
    }

    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');

        // signature midtrans
        $hashed = hash('sha512',
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($hashed !== $request->signature_key) {
            return response()->json([
                'message' => 'Invalid signature'
            ], 403);
        }

        $transaction = Transactions::where('order_id', $request->order_id)->first();

        if (!$transaction) return;

        $status = $request->transaction_status;

        if ($status === 'capture' || $status === 'settlement') {
            $transaction->update(['status' => 'success']);
        } else if ($status === 'deny' || $status === 'cancel' || $status === 'expire') {
            $transaction->update(['status' => 'failed']);
        } else if ($status === 'pending') {
            $transaction->update(['status' => 'pending']);
        }

        return response()->json([
            'message' => 'Callback processed'
        ]);
    }
}