<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function getPaket()
    {
        $pakets = Paket::all();

        return response()->json([   
            'status' => 'success',
            'message' => 'List of all pakets',
            'data' => $pakets,
        ]);
    }
}
