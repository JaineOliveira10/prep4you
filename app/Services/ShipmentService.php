<?php

namespace App\Services;

use App\Repositories\ShipmentRepository;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Helpers\BusinessDaysHelper;

class ShipmentService
{
    protected $shipmentRepository;

    public function __construct(ShipmentRepository $shipmentRepository)
    {
        $this->shipmentRepository = $shipmentRepository;
    }

    public function getAll()
    {
        return $this->shipmentRepository->all();
    }

    public function findById($id)
    {
        return $this->shipmentRepository->find($id);
    }

    public function create(array $data)
    {
        // Calcular collection_date automaticamente se não fornecida
        if (!isset($data['collection_date']) && isset($data['shipment_date'])) {
            $collectionDate = BusinessDaysHelper::addBusinessDays($data['shipment_date'], 3);
            $data['collection_date'] = $collectionDate->format('Y-m-d');
        }
        
        $shipment = $this->shipmentRepository->create([
            'shipment_date' => $data['shipment_date'],
            'collection_date' => $data['collection_date'],
            'status' => $data['status'] ?? 'Pending',
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'distribution_center_id' => $data['distribution_center_id'],
            'creation_date' => $data['creation_date'],
        ]);

        // Criar itens da remessa se fornecidos
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!empty($item['product_id']) && !empty($item['quantity'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($product) {
                        ShipmentItem::create([
                            'shipment_id' => $shipment->id,
                            'product_id' => $item['product_id'],
                            'name' => $product->name,
                            'fsnku' => $product->fsnku,
                            'sku' => $product->sku,
                            'type' => $product->type,
                            'kit_units' => $product->kit_units,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_value' => $item['quantity'] * ($item['unit_price'] ?? 0)
                        ]);
                    }
                }
            }
        }

        return $shipment;
    }

    public function update($id, array $data)
    {
        // Buscar remessa atual para preservar creation_date
        $currentShipment = $this->shipmentRepository->find($id);
        
        // Calcular collection_date automaticamente se não fornecida
        if (!isset($data['collection_date']) && isset($data['shipment_date'])) {
            $collectionDate = BusinessDaysHelper::addBusinessDays($data['shipment_date'], 3);
            $data['collection_date'] = $collectionDate->format('Y-m-d');
        }
        
        $shipment = $this->shipmentRepository->update($id, [
            'shipment_date' => $data['shipment_date'],
            'collection_date' => $data['collection_date'],
            'status' => $data['status'] ?? 'Pending',
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'distribution_center_id' => $data['distribution_center_id'],
            'creation_date' => $currentShipment->creation_date,
        ]);

        // Atualizar itens da remessa
        if (isset($data['items']) && is_array($data['items'])) {
            // Remover itens existentes
            ShipmentItem::where('shipment_id', $id)->delete();
            
            // Criar novos itens
            foreach ($data['items'] as $item) {
                if (!empty($item['product_id']) && !empty($item['quantity'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($product) {
                        ShipmentItem::create([
                            'shipment_id' => $id,
                            'product_id' => $item['product_id'],
                            'name' => $product->name,
                            'fsnku' => $product->fsnku,
                            'sku' => $product->sku,
                            'type' => $product->type,
                            'kit_units' => $product->kit_units,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_value' => $item['quantity'] * ($item['unit_price'] ?? 0)
                        ]);
                    }
                }
            }
        }

        return $shipment;
    }

    public function delete($id)
    {
        return $this->shipmentRepository->delete($id);
    }
}
