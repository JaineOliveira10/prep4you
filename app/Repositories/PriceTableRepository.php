<?php

namespace App\Repositories;

use App\Models\PriceTable;
use App\Interfaces\PriceTableRepositoryInterface;

class PriceTableRepository implements PriceTableRepositoryInterface
{
    protected $model;

    public function __construct(PriceTable $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $priceTable = $this->find($id);
        $priceTable->update($data);
        return $priceTable;
    }

    public function delete($id)
    {
        $priceTable = $this->find($id);
        return $priceTable->delete();
    }
}
