<?php

namespace App\Http\Controllers\Admin;

use App\Models\Chat;
use App\Models\User;
use App\Models\Paket;
use App\Models\Rating;
use App\Models\Silabus;
use App\Models\Reschedule;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    // counting
    public function countCustomerAktif()
    {
        $customerAktif = User::role('customer')->count();

        return response()->json([
            'status' => 'ok',
            'message' => 'List customer aktif',
            'data' => $customerAktif
        ]);
    }

    // bagian list payment
    public function getPayment()
    {
        $transactions = Transactions::with(['user', 'paket'])->get();

        return response()->json([
            'message' => 'List Payment user',
            'data' => $transactions
        ]);
    }

    // bagian schedule
    public function getPaketAdmin()
    {
        $pakets = Paket::paginate(5);

        return response()->json([
            'status' => 'success',
            'message' => 'List Paket for Admin',
            'data' => $pakets,
        ], 200);
    }

    public function storeSilabus(Request $request, $paketId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'option_change' => 'nullable|date',
        ]);

        $pakets = Paket::findOrFail($paketId);

        $silabus = $pakets->silabus()->create([
            'title' => $request->title,
            'date' => $request->date,
            'time' => $request->time,
            'option_change' => $request->option_change,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule created successfully',
            'data' => $silabus,
        ], 201);
    }

    public function getSilabus($paketId)
    {
        $paket = Paket::with('silabus')->findOrFail($paketId);

        if (!$paket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paket tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'List Silabus for Paket ID: ' . $paketId,
            'data' => $paket,
        ], 200);
    }

    // bagian reschedule
    public function getReschedule()
    {
        $reSchedule = Reschedule::with(['user', 'paket', 'transaction'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $reSchedule
        ]);
    }

    public function approveReschedule($id)
    {
        $res = Reschedule::findOrFail($id);

        $tanggalBaru = $res->tanggal;

        // update data transaction
        $transactions = Transactions::find($res->transaction_id);

        if($transactions) {
            $transactions->tanggal = $tanggalBaru;
            $transactions->save();
        }

        $res->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reschedule Approved'
        ]);
    }

    public function rejectReschedule($id)
    {
        $res = Reschedule::find($id);

        if(!$res) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found!'
            ], 404);
        }

        $res->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'REschedule Rejected'
        ]);
    }

    // bagian chat
    public function getChat($customerId)
    {
        $messages = Chat::where('customer_id', $customerId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender' => $msg->sender ?? ($msg->from_admin ? "admin" : "customer"),
                    'created_at' => $msg->created_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Chat history with customer ID: ' . $customerId,
            'data' => $messages,
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'message' => 'required',
        ]);

        $chat = Chat::create([
            'customer_id' => $request->customer_id,
            'sender' => 'admin',
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $chat,
        ], 201);
    }

    public function getAllCustomer()
    {
        $customers = User::role('customer')->paginate(5);

        return response()->json([
            'status' => 'success',
            'message' => 'List Customer',
            'data' => $customers,
        ], 200);
    }

    
}