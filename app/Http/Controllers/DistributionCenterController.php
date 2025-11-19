<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionCenterRequest;
use App\Services\DistributionCenterService;

class DistributionCenterController extends Controller
{
    
    protected $distributionCenterService;

    public function __construct(DistributionCenterService $distributionCenterService)
    {
        $this->distributionCenterService = $distributionCenterService;
    }

    public function index()
    {
        $distributionCenters = $this->distributionCenterService->getAll();
        $assets = ['data-table'];
        return view('pages.distribution-centers.index', compact('distributionCenters', 'assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('distribution-centers.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DistributionCenterRequest $request)
    {
        $this->distributionCenterService->create($request->validated());
        return redirect()->route('pages.distribution-centers.index')->with('success', 'Centro de distribuição criado com sucesso!');
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
        $data = $this->distributionCenterService->findById($id);
        return view('distribution-centers.form', compact('data', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DistributionCenterRequest $request, string $id)
    {
        $this->distributionCenterService->update($id, $request->validated());
        return redirect()->route('pages.distribution-centers.index')->with('success', 'Centro de distribuição atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->distributionCenterService->delete($id);

        return redirect()
            ->route('pages.distribution-centers.index')
            ->withSuccess(__('Centro de distribuição removido com sucesso.'));
    }
}
