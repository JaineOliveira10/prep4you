<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Shipment;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::user()->client_id;

        $productsWithoutPhoto = Product::where('client_id', $client)
            ->whereNull('photo_path')
            ->count();

        $remessesWithIssues = Shipment::where('client_id', $client)
            ->where('status', 'Has Pendency')
            ->count();

        $unpaidClosures = 0;


        return view('dashboards.dashboard', compact('productsWithoutPhoto', 'remessesWithIssues', 'unpaidClosures'));
    }
}
