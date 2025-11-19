<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriceTableRequest;
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
        return view('pages.price-tables.index', compact('priceTables', 'assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.price-tables.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PriceTableRequest $request)
    {
        $this->priceTableService->create($request->validated());
        return redirect()->route('price-tables.index')->with('success', 'Tabela de preço criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $priceTable = $this->priceTableService->findById($id);
        return view('pages.price-tables.show', compact('priceTable'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->priceTableService->findById($id);
        return view('pages.price-tables.form', compact('data', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceTableRequest $request, string $id)
    {
        $this->priceTableService->update($id, $request->validated());
        return redirect()->route('price-tables.index')->with('success', 'Tabela de preço atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->priceTableService->delete($id);
            return redirect()
                ->route('price-tables.index')
                ->withSuccess(__('Tabela de preço removida com sucesso.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('price-tables.index')
                ->withError($e->getMessage());
        }
    }
}
