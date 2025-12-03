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
        $user = auth()->user();

        // Query base
        $query = Shipment::query();

        // Filtro por cliente
        if ($user->type === 'client') {
            $query->where('client_id', $user->client_id);
            $clients = collect([$user->client]);
        } else {
            if (request('client_id')) {
                $query->where('client_id', request('client_id'));
            }
            $clients = Client::all();
        }
        
        // Filtro por status
        if (request('status')) {
            $query->where('status', request('status'));
        }
        
        // Filtro por data
        $dateFilter = request('date_filter', 'created_at');
        $dateFrom = request('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = request('date_to', now()->format('Y-m-d'));
        
        if ($dateFrom) {
            $query->whereDate($dateFilter, '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate($dateFilter, '<=', $dateTo);
        }
        
        $shipments = $query->orderBy('created_at', 'desc')->get();
        
        return view('pages.shipments.index', [
            'shipments' => $shipments,
            'clients' => $clients,
            'assets' => []
        ]);
    }

    public function manageShipments(Request $request)
    {
        $user = auth()->user();
        $distribution_centers = DistributionCenter::all();
        
        // Query base
        $query = Shipment::query();
        
        // Filtro por cliente
        if ($user->type === 'client') {
            $query->where('client_id', $user->client_id);
            $clients = collect([$user->client]);
        } else {
            if (request('client_id')) {
                $query->where('client_id', request('client_id'));
            }
            $clients = Client::all();
        }
        
        // Filtro por status
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Filtro por centro de distribuição
        if (request('distribution_center_id')) {
            $query->where('distribution_center_id', request('distribution_center_id'));
        }
        
        // Filtro por data
        $dateFilter = request('date_filter', 'created_at');
        $dateFrom = request('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = request('date_to', now()->format('Y-m-d'));
        
        if ($dateFrom) {
            $query->whereDate($dateFilter, '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate($dateFilter, '<=', $dateTo);
        }
        
        $shipments = $query->orderBy('created_at', 'desc')->get();
        
        return view('pages.shipments.index-admin', [
            'shipments' => $shipments,
            'clients' => $clients,
            'distribution_centers' => $distribution_centers,
            'assets' => []
        ]);
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
        $shipment = $this->shipmentService->create($data);
        
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
        try {
            $productId = $request->input('product_id');
            $fsnku = $request->input('fsnku');
            $sku = $request->input('sku');
            $quantity = $request->input('quantity', 1);
            $type = $request->input('type', 'simple');
            
            // Aceitar client_id do request ou usar o do usuário autenticado
            $clientId = $request->input('client_id') ?? auth()->user()->client->id;
            
            \Log::info('getProductPrice chamado', [
                'product_id' => $productId,
                'fsnku' => $fsnku,
                'sku' => $sku,
                'type' => $type,
                'quantity' => $quantity,
                'clientId' => $clientId
            ]);
            
            // Buscar produto pelo ID ou FSNKU ou SKU
            $product = null;
            
            if ($productId) {
                $product = Product::where('id', $productId)
                    ->where('client_id', $clientId)
                    ->where('type', $type)
                    ->first();
                
                // Fallback: buscar sem filtro de tipo se não encontrou
                if (!$product) {
                    $product = Product::where('id', $productId)
                        ->where('client_id', $clientId)
                        ->first();
                }
            } elseif ($fsnku) {
                $product = Product::where('fsnku', $fsnku)
                    ->where('client_id', $clientId)
                    ->where('type', $type)
                    ->first();
                
                // Fallback: buscar sem filtro de tipo se não encontrou
                if (!$product) {
                    $product = Product::where('fsnku', $fsnku)
                        ->where('client_id', $clientId)
                        ->first();
                }
            } elseif ($sku) {
                $product = Product::where('sku', $sku)
                    ->where('client_id', $clientId)
                    ->where('type', $type)
                    ->first();
                
                // Fallback: buscar sem filtro de tipo se não encontrou
                if (!$product) {
                    $product = Product::where('sku', $sku)
                        ->where('client_id', $clientId)
                        ->first();
                }
            }
            
            if (!$product) {
                \Log::warning('Produto não encontrado', [
                    'product_id' => $productId,
                    'fsnku' => $fsnku,
                    'sku' => $sku,
                    'type' => $type,
                    'clientId' => $clientId
                ]);
                return response()->json(['price' => 0, 'error' => 'Produto não encontrado']);
            }
            
            \Log::info('Produto encontrado', [
                'product_id' => $product->id,
                'fsnku' => $product->fsnku,
                'type' => $product->type
            ]);
            
            // Usar o service para buscar o preço
            $result = $this->shipmentService->getProductPrice(
                $product->id, 
                $clientId, 
                $quantity, 
                $product->type
            );
            
            $price = (float)($result['price'] ?? 0);
            
            \Log::info('Preço retornado', [
                'price' => $price,
                'product_id' => $product->id,
                'type' => $product->type
            ]);
            
            return response()->json(['price' => $price]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao obter preço', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['price' => 0, 'error' => $e->getMessage()]);
        }
    }

    public function preview(ShipmentImportRequest $request)
    {
        try {
            $file = $request->file('tsv_file');
            $clientId = auth()->user()->client->id;

            $result = $this->shipmentService->previewTsv($file);

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 422);
            }

            $products = $result['products'] ?? [];
            foreach ($products as &$product) {
                $exists = Product::where('client_id', $clientId)
                    ->where('fsnku', $product['fsnku'])
                    ->exists();
                $product['exists'] = $exists;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $result['data'],
                    'products' => $products
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function import(Request $request)
    {
        try {
            $request->validate([
                'tsv_file' => 'required|file|mimes:tsv,txt',
                'shipment_date' => 'required|date'
            ]);

            $productsData = json_decode($request->input('products_data'), true) ?? [];

            $result = $this->shipmentService->importFromTsv(
                $request->file('tsv_file'),
                auth()->user()->client->id,
                $request->input('shipment_date'),
                $productsData
            );

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], $result['code'] ?? 422);
            }

            return response()->json([
                'success' => true,
                'shipment_id' => $result['shipment']->id,
                'message' => 'Remessa criada com sucesso'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => collect($e->errors())->flatten()->first() ?? 'Erro de validação'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao importar remessa: ' . $e->getMessage(), ['exception' => $e]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
