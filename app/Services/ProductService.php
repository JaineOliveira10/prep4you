<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAll()
    {
        return $this->productRepository->all();
    }

    public function getByClient($clientId = null)
    {
        if ($clientId) {
            return $this->productRepository->getByClient($clientId);
        }
        return $this->productRepository->all();
    }

    public function findById($id)
    {
        return $this->productRepository->find($id);
    }

    public function create(array $data)
    {
        $photoPath = null;
        if (isset($data['photo']) && $data['photo']) {
            $photoPath = $data['photo']->store('products', 'public');
        }

        $productData = [
            'name' => $data['name'],
            'asin' => $data['asin'] ?? null,
            'fsnku' => $data['fsnku'] ?? null,
            'sku' => $data['sku'] ?? null,
            'photo_path' => $photoPath,
            'observation' => $data['observation'] ?? null,
            'type' => $data['type'],
            'kit_units' => $data['type'] != 'simple' ? $data['kit_units'] : null,
            'unit_price' => $data['type'] == 'super_kit' ? $data['unit_price'] : null,
            'client_id' => $data['client_id']
        ];

        return $this->productRepository->create($productData);
    }

    public function update($id, array $data)
    {
        $product = $this->findById($id);
        $photoPath = $product->photo_path;
        
        if (isset($data['photo']) && $data['photo']) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $data['photo']->store('products', 'public');
        }

        $productData = [
            'name' => $data['name'],
            'asin' => $data['asin'] ?? null,
            'fsnku' => $data['fsnku'] ?? null,
            'sku' => $data['sku'] ?? null,
            'photo_path' => $photoPath,
            'observation' => $data['observation'] ?? null,
            'type' => $data['type'],
            'kit_units' => $data['type'] != 'simple' ? $data['kit_units'] : null,
            'unit_price' => $data['type'] == 'super_kit' ? $data['unit_price'] : null,
            'client_id' => $data['client_id']
        ];

        return $this->productRepository->update($id, $productData);
    }

    public function delete($id)
    {
        $product = $this->findById($id);
        if ($product->photo_path) {
            Storage::disk('public')->delete($product->photo_path);
        }
        return $this->productRepository->delete($id);
    }
}
