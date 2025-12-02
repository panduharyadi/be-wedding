<?php

namespace App\Http\Controllers\Customer;

use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reschedule;
use Illuminate\Support\Facades\Auth;

class RequestJadwalController extends Controller
{
    public function requestJadwal(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:pakets,id',
            'tanggal' => 'required|date',
            'jam' => 'required',
            'alasan' => 'nullable|string'
        ]);

        $user = Auth::user();

        $purchased = Transactions::where('user_id', $user->id)
                        ->where('paket_id', $request->paket_id)
                        ->where('status', 'success')
                        ->first();

        if (!$purchased) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki paket ini atau pembayaran belum berhasil.'
            ], 403);
        }

        $schedule = Reschedule::create([
            'user_id' => $user->id,
            'paket_id' => $request->paket_id,
            'transaction_id' => $purchased->id,
            'tanggal' => $request->tanggal,
            'jam' => $request->jam,
            'alasan' => $request->alasan,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request jadwal berhasil dibuat.',
            'data' => $schedule
        ]);
    }
}
