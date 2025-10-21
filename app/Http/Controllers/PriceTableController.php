<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PriceTableService;

class PriceTableController extends Controller
{
    protected $priceTableService;

    public function __construct(PriceTableService $priceTableService)
    {
        $this->priceTableService = $priceTableService;
    }

    public function index()
    {
        $priceTables = $this->priceTableService->getAll();
        $assets = ['data-table'];
        return view('price-tables.index', compact('priceTables', 'assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('price-tables.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
