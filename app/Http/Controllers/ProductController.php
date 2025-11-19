<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Requests\ProductRequest;
use App\Models\Client;

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
        
        $product = $this->productService->create($request->validated());
        return redirect()->route('pages.products.index')->with('success', 'Produto criado com sucesso!');
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
        return redirect()->route('pages.products.index')->with('success', 'Produto atualizado com sucesso!');
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
        
        $this->productService->delete($id);
        return redirect()->route('pages.products.index')->with('success', 'Produto excluído com sucesso!');
    }
}
