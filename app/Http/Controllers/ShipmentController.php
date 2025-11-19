<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ShipmentRequest;
use App\Http\Requests\ShipmentImportRequest;
use App\Services\ShipmentService;
use App\Models\Client;
use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\PriceTable;
use App\Models\PriceRange;
use App\Models\Shipment;

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
        return view('pages.shipments.index', compact('shipments', 'clients', 'distributionCenters', 'assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = [];
        if (auth()->user()->type === 'client' && auth()->user()->client) {
            $products = Product::where('client_id', auth()->user()->client->id)->get();
        }
        return view('pages.shipments.form', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShipmentRequest $request)
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
        return view('pages.shipments.show', compact('shipment', 'assets'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->shipmentService->findById($id);
        $products = [];
        if (auth()->user()->type === 'client' && auth()->user()->client) {
            $products = Product::where('client_id', auth()->user()->client->id)->get();
        } elseif (auth()->user()->type === 'admin' && $data->client_id) {
            $products = Product::where('client_id', $data->client_id)->get();
        }
        return view('pages.shipments.form', compact('data', 'id', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShipmentRequest $request, string $id)
    {
        $this->shipmentService->update($id, $request->all());
        return redirect()->route('shipments.index')->with('success', 'Remessa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (auth()->user()->type != 'client') {
            abort(403, 'Apenas clientes podem excluir remessas.');
        }
        
        $shipment = $this->shipmentService->findById($id);
        
        if ($shipment->client_id != auth()->user()->client_id) {
            abort(403, 'Você não pode excluir esta remessa.');
        }
        
        $this->shipmentService->delete($id);
        return redirect()->route('shipments.index')->with('success', 'Remessa excluída com sucesso!');
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

    /**
     * Get products by client
     */
    public function getProductsByClient(Request $request)
    {
        $clientId = $request->input('client_id');
        $products = Product::where('client_id', $clientId)->get();
        
        return response()->json($products);
    }

    /**
     * Get product price based on client's price table
     */
    public function getProductPrice(Request $request)
    {
        $productId = $request->input('product_id');
        $clientId = $request->input('client_id');
        $quantity = $request->input('quantity', 1);
        
        $result = $this->shipmentService->getProductPrice($productId, $clientId, $quantity);
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 404);
        }
        
        return response()->json($result);
    }

    public function preview(ShipmentImportRequest $request)
    {
        try {
            $result = $this->shipmentService->previewTsv($request->file('tsv_file'));
            
            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 422);
            }
            
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'tsv_file' => 'required|file|mimes:tsv,txt',
            'shipment_date' => 'required|date'
        ]);
        
        try {
            $result = $this->shipmentService->importFromTsv(
                $request->file('tsv_file'), 
                auth()->user()->client->id,
                $request->input('shipment_date')
            );
            
            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 422);
            }
            
            return response()->json([
                'success' => true,
                'shipment' => $result['shipment']
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
}
