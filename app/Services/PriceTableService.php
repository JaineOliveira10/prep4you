<?php

namespace App\Services;

use App\Repositories\PriceTableRepository;

class PriceTableService
{
    protected $priceTableRepository;

    public function __construct(PriceTableRepository $priceTableRepository)
    {
        $this->priceTableRepository = $priceTableRepository;
    }

    public function getAll()
    {
        return $this->priceTableRepository->all();
    }

    public function findById($id)
    {
        return $this->priceTableRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->priceTableRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->priceTableRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->priceTableRepository->delete($id);
    }
}
