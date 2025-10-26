<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Product;

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

    public function findById($id)
    {
        return $this->productRepository->find($id);
    }

    public function create(array $data)
    {
        $product = $this->productRepository->create([
            'name' => $data['name'],
            'asin' => $data['asin'], 
            'fsnku' => $data['fsnku'], 
            'sku' => $data['sku'], 
            'photo_path' => $data['photo_path'], 
            'observation' => $data['observation'], 
            'type' => $data['type'], 
            'kit_units' => $data['kit_units'], 
            'unit_price' => $data['unit_price'], 
            'client_id' => $data['client_id']
        ]);

        return $product;
    }

    public function update($id, array $data)
    {
        $product = $this->productRepository->update($id, [
            'name' => $data['name'],
            'asin' => $data['asin'], 
            'fsnku' => $data['fsnku'], 
            'sku' => $data['sku'], 
            'photo_path' => $data['photo_path'], 
            'observation' => $data['observation'], 
            'type' => $data['type'], 
            'kit_units' => $data['kit_units'], 
            'unit_price' => $data['unit_price'], 
            'client_id' => $data['client_id']
        ]);

        return $product;
    }

    public function delete($id)
    {
        return $this->productRepository->delete($id);
    }
}
