<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\MonthlyClosure;
use Illuminate\Support\Facades\DB;

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

       
        $unpaidClosuresList = MonthlyClosure::unpaidByClient($client)
            ->with(['closureClient' => function ($q) use ($client) {
                $q->where('client_id', $client)
                ->where('paid_flag', false);
            }])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();


        $unpaidClosures = $unpaidClosuresList->count();

        $totalNetUnpaid = DB::table('monthly_closure_clients')
            ->where('client_id', $client)
            ->where('paid_flag', false)
            ->sum('total_net');



        return view('dashboards.dashboard', compact('productsWithoutPhoto', 'remessesWithIssues', 'unpaidClosures', 'unpaidClosuresList', 'totalNetUnpaid', 'client'));
    }
}
