<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{

    public function makeBooking(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'endpoint ok'
        ]);
    }
}
