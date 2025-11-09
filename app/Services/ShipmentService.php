<?php

namespace App\Services;

use App\Repositories\ShipmentRepository;
use App\Models\Shipment;
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
            'status' => $data['status'],
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'distribution_center_id' => $data['distribution_center_id'],
            'creation_date' => $data['creation_date'],
        ]);

        return $shipment;
    }

    public function update($id, array $data)
    {
        $shipment = $this->shipmentRepository->update($id, [
            'shipment_date' => $data['shipment_date'],
            'collection_date' => $data['collection_date'],
            'status' => $data['status'],
            'name' => $data['name'],
            'client_id' => $data['client_id'],
            'distribution_center_id' => $data['distribution_center_id'],
            'creation_date' => $data['creation_date'] ?? null,
        ]);

        return $shipment;
    }

    public function delete($id)
    {
        return $this->shipmentRepository->delete($id);
    }
}
