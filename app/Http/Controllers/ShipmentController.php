<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShipmentService;
use App\Models\Client;
use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\PriceTable;
use App\Models\PriceRange;

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
        $products = [];
        if (auth()->user()->type === 'client' && auth()->user()->client) {
            $products = Product::where('client_id', auth()->user()->client->id)->get();
        }
        return view('shipments.form', compact('products'));
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
        $products = [];
        if (auth()->user()->type === 'client' && auth()->user()->client) {
            $products = Product::where('client_id', auth()->user()->client->id)->get();
        } elseif (auth()->user()->type === 'admin' && $data->client_id) {
            $products = Product::where('client_id', $data->client_id)->get();
        }
        return view('shipments.form', compact('data', 'id', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->shipmentService->update($id, $request->all());
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
        $quantity = $request->input('quantity', 1); // Quantidade padrão = 1
        
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }
        
        // Para super_kit, sempre usar o preço do campo preço do produto
        if ($product->type === 'super_kit') {
            $price = $product->unit_price ?? 0;
        } else {
            // Para produtos simples e kit, buscar na tabela de preços do cliente baseado na quantidade
            $client = Client::find($clientId);
            
            if (!$client || !$client->price_table_id) {
                $price = $product->unit_price ?? 0;
            } else {
                // Buscar range baseado na quantidade
                $priceRange = PriceRange::where('price_table_id', $client->price_table_id)
                    ->where('min_value', '<=', $quantity)
                    ->where('max_value', '>=', $quantity)
                    ->first();
                
                if ($priceRange) {
                    if ($product->type === 'kit') {
                        $price = $priceRange->price_kit ?? $priceRange->price;
                    } else {
                        $price = $priceRange->price;
                    }
                } else {
                    // Se não encontrar range, usar o primeiro disponível
                    $priceRange = PriceRange::where('price_table_id', $client->price_table_id)
                        ->orderBy('min_value')
                        ->first();
                    
                    if ($priceRange) {
                        if ($product->type === 'kit') {
                            $price = $priceRange->price_kit ?? $priceRange->price;
                        } else {
                            $price = $priceRange->price;
                        }
                    } else {
                        $price = $product->unit_price ?? 0;
                    }
                }
            }
        }
        
        return response()->json([
            'price' => $price,
            'product' => $product
        ]);
    }
}
