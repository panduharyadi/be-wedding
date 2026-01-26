<?php

namespace App\Http\Controllers\Customer;

use App\Models\Paket;
use App\Models\Reschedule;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RequestJadwalController extends Controller
{
    public function getPaketSchedules($paketId)
    {
        $paket = Paket::with('schedules')->findOrFail($paketId);

        return response()->json([
            'status' => 'success',
            'data' => $paket->schedules
        ]);
    }

    public function requestJadwal(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:pakets,pk_paket_id',
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
            'transaction_id' => $purchased->pk_transaction_id,
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
