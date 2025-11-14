<?php

namespace App\Repositories;

use App\Models\Shipment;
use App\Interfaces\ShipmentRepositoryInterface;

class ShipmentRepository implements ShipmentRepositoryInterface
{
    protected $model;

    public function __construct(Shipment $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->with('client', 'distributionCenter')->get();
    }

    public function find($id)
    {
        return $this->model->with(['pdfs', 'items'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $shipment = $this->find($id);
        $shipment->update($data);
        return $shipment;
    }

    public function delete($id)
    {
        $shipment = $this->find($id);
        return $shipment->delete();
    }
}
