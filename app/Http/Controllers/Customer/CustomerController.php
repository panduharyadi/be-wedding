<?php

namespace App\Http\Controllers\Customer;

use App\Models\Chat;
use App\Models\Certificate;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Halo Customer! Ini dashboard khusus customer.'
        ]);
    }

    public function getPurchasingPaket()
    {
        $userId = Auth::id();

        $purchase = Transactions::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $purchase,
        ]);
    }

    public function getPurchasedSilabus()
    {
        $userId = Auth::id();

        $transactions = Transactions::with('paket.silabus')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $silabus = $transactions->map(function ($trx) {
            return $trx->paket->silabus;
        })->flatten();

        return response()->json([
            'status' => 'success',
            'data' => $silabus,
        ]);
    }

    public function getChat(Request $request)
    {
        $messages = Chat::where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'from_admin' => $msg->sender === "admin",
                    'time' => $msg->created_at->format("H:i"),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'message'     => 'required|string',
        ]);

        Chat::create([
            'customer_id' => $request->customer_id,
            'sender' => 'customer',
            'message'     => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save(); 
        
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    // sertifikat
    public function myCertificate(Request $request)
    {
        $user = $request->user();

        $certificate = Certificate::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => $certificate
        ]);
    }
}
