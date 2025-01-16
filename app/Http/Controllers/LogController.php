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

    /**
     * Saves a log message to the specified log channel.
     *
     * This function logs an error message to a specific log channel based on the provided log type.
     * If the log type is not recognized, it logs an error message to the default channel.
     *
     * @param string $logtype The type of log channel to use (must be one of the predefined LOGTYPES)
     * @param string $method The method or context where the log is being generated
     * @param string $message The actual log message to be saved
     *
     * @return void This function does not return a value
     */
    public static function saveLog(string $logtype,string $method,string $message)
    {
        if(in_array($logtype,static::LOGTYPES)){
            Log::channel($logtype.'_log')->error($method.': '.$message);
        } else {
            Log::error('This error message was logged with wrong log type: '.$method.': '.$message);
        }
    }
}
