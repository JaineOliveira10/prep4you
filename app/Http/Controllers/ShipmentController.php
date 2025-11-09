<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShipmentService;
use App\Models\Client;
use App\Models\DistributionCenter;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    public function index(Request $request)
    {
        $shipments = $this->shipmentService->getAll();
        
        if (auth()->user()->type === 'client' && auth()->user()->client) {
            $shipments = $shipments->where('client_id', auth()->user()->client->id);
        }
        
        if ($request->filled('client_id')) {
            $shipments = $shipments->where('client_id', $request->client_id);
        }
        
        $clients = Client::all();
        $distributionCenters =  DistributionCenter::all();
        $assets = ['data-table'];
        return view('shipments.index', compact('shipments', 'clients', 'distributionCenters', 'assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shipments.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['creation_date'] = now()->format('Y-m-d');
        $this->shipmentService->create($data);
        return redirect()->route('shipments.index')->with('success', 'Remessa criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shipment = $this->shipmentService->findById($id);
        $assets = [];
        return view('shipments.show', compact('shipment', 'assets'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->shipmentService->findById($id);
        return view('shipments.form', compact('data', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->shipmentService->update($id, $request->validated());
        return redirect()->route('shipments.index')->with('success', 'Remessa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->distributionCenterService->delete($id);

        return redirect()
            ->route('shipments.index')
            ->withSuccess(__('Remessa removida com sucesso.'));
    }

    /**
     * Calculate collection date based on shipment date
     */
    public function calculateCollectionDate(Request $request)
    {
        $shipmentDate = $request->input('shipment_date');
        
        if ($shipmentDate) {
            $collectionDate = \App\Helpers\BusinessDaysHelper::addBusinessDays($shipmentDate, 3);
            return response()->json([
                'collection_date' => $collectionDate->format('Y-m-d')
            ]);
        }
        
        return response()->json(['error' => 'Data inválida'], 400);
    }
}
