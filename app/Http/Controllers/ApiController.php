<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\APIMonitor;

class ApiController extends Controller
{
    //GET /api/status
    public function index()
    {
        $apiMonitor = new APIMonitor();
        
        // Kiểm tra trạng thái của API 1
        $api1Status = $apiMonitor->checkAPI('http://127.0.0.1:8000/login');

        // Kiểm tra trạng thái của API 2
        $api2Status = $apiMonitor->checkAPI('http://127.0.0.1:8000/save-email');

        return view('api.index', [
            'api1Status' => $api1Status,
            'api2Status' => $api2Status,
        ]);
    }
}
