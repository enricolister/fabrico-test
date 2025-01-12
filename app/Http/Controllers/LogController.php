<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    private const LOGTYPES = [
        'auth',
        'booking_api',
        'jobs'
    ];

    public static function saveLog(string $logtype,string $method,string $message)
    {
        if(in_array($logtype,static::LOGTYPES)){
            Log::channel($logtype.'_log')->error($method.': '.$message);
        } else {
            Log::error('This error message was logged with wrong log type: '.$method.': '.$message);
        }
    }
}
