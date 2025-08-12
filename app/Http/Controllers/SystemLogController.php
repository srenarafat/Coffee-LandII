<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;

class SystemLogController extends Controller
{
    public function index()
    {
        $logs = SystemLog::with('user')->latest()->paginate(20);
        return view('system_logs.index', compact('logs'));
    }
}