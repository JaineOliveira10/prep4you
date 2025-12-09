<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Services\ProductService;
use App\Http\Requests\ProductRequest;
use App\Models\Client;
use App\Models\Product;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $clients = [];
        $products = [];
        
        if (auth()->user()->type == 'admin') {
            $clients = Client::all();
            $products = $this->productService->getByClient($request->client_id);
        } else {
            $clients = Client::where('id', auth()->user()->client_id)->get();
            $products = $this->productService->getByClient(auth()->user()->client_id);
        }
        
        $assets = ['data-table'];
        return view('pages.products.index', compact('products', 'assets', 'clients'));
    }

    public function create()
    {
        if (auth()->user()->type != 'client') {
            abort(403, 'Apenas clientes podem criar produtos.');
        }
        
        $clients = auth()->user()->type == 'admin' ? Client::all() : [];
        $assets = [];
        return view('pages.products.form', compact('assets', 'clients'));
    }

    public function copy(string $id)
    {
        if (auth()->user()->type != 'client') {
            abort(403, 'Apenas clientes podem copiar produtos.');
        }
        
        $product = $this->productService->findById($id);
        
        if ($product->client_id != auth()->user()->client_id) {
            abort(403, 'Você não pode copiar este produto.');
        }
        
        $clients = auth()->user()->type == 'admin' ? Client::all() : [];
        $data = $product;
        $assets = [];
        return view('pages.products.form', compact('data', 'assets', 'clients'));
    }

    public function store(ProductRequest $request)
    {
        if (auth()->user()->type != 'client') {
            abort(403, 'Apenas clientes podem criar produtos.');
        }

        try {
            $product = $this->productService->create($request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'product' => $product,
                    'message' => 'Produto criado com sucesso!'
                ]);
            }

            return redirect()
                ->route('products.index')
                ->with('success', 'Produto criado com sucesso!');

        } catch (QueryException $e) {

            if ($e->getCode() == 23505) {
                $message = 'Já existe um produto cadastrado com algum destes valores únicos FSNKU, Código).';

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 422);
                }

                return back()->withErrors(['error' => $message]);
            }

            // Outros erros de banco
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro no banco de dados.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Erro no banco de dados.']);
        
        } catch (\Exception $e) {

            // Erros gerais
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao criar produto.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Erro ao criar produto.']);
        }
    }


    public function show(string $id)
    {
        $product = $this->productService->findById($id);
        $assets = [];
        return view('pages.products.show', compact('product', 'assets'));
    }

    public function edit(string $id)
    {
        $product = $this->productService->findById($id);
        
        if (auth()->user()->type == 'client' && $product->client_id != auth()->user()->client_id) {
            abort(403, 'Você não pode editar este produto.');
        }
        
        $clients = auth()->user()->type == 'admin' ? Client::all() : [];
        $data = $product;
        $assets = [];
        return view('pages.products.form', compact('data', 'id', 'assets', 'clients'));
    }

    public function update(ProductRequest $request, string $id)
    {
        $product = $this->productService->findById($id);
        
        if (auth()->user()->type == 'client' && $product->client_id != auth()->user()->client_id) {
            abort(403, 'Você não pode editar este produto.');
        }
        
        $this->productService->update($id, $request->validated());
        return redirect()->route('products.index')->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        if (auth()->user()->type != 'client') {
            abort(403, 'Apenas clientes podem excluir produtos.');
        }
        
        $product = $this->productService->findById($id);
        
        if ($product->client_id != auth()->user()->client_id) {
            abort(403, 'Você não pode excluir este produto.');
        }
        
        // Verificar se o produto está em alguma remessa
        $hasShipments = \App\Models\ShipmentItem::where('product_id', $id)->exists();
        
        if ($hasShipments) {
            return redirect()->route('products.index')->with('error', 'Não é possível excluir este produto pois ele está vinculado a uma ou mais remessas.');
        }
        
        $this->productService->delete($id);
        return redirect()->route('products.index')->with('success', 'Produto excluído com sucesso!');
    }
    
    public function storeAjax(ProductRequest $request)
    {
        if (auth()->user()->type != 'client') {
            return response()->json(['success' => false, 'error' => 'Apenas clientes podem criar produtos.'], 403);
        }
        
        try {
            $product = $this->productService->create($request->validated());
            
            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Produto criado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar produto: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Retornar dados do produto em JSON
     */
    public function getJson(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'fsnku' => $product->fsnku,
                'sku' => $product->sku
            ]
        ]);
    }
}
