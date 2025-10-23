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
        $priceTable = $this->priceTableRepository->create([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        if (isset($data['ranges'])) {
            foreach ($data['ranges'] as $range) {
                $priceTable->priceRanges()->create([
                    'min_value' => $range['min_value'],
                    'max_value' => $range['max_value'],
                    'price' => str_replace(',', '.', $range['price']),
                    'price_kit' => str_replace(',', '.', $range['price_kit']),
                ]);
            }
        }

        return $priceTable;
    }

    public function update($id, array $data)
    {
        $priceTable = $this->priceTableRepository->update($id, [
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        $priceTable->priceRanges()->delete();

        if (isset($data['ranges'])) {
            foreach ($data['ranges'] as $range) {
                $priceTable->priceRanges()->create([
                    'min_value' => $range['min_value'],
                    'max_value' => $range['max_value'],
                    'price' => str_replace(',', '.', $range['price']),
                    'price_kit' => str_replace(',', '.', $range['price_kit']),
                ]);
            }
        }

        return $priceTable;
    }

    public function delete($id)
    {
        return $this->priceTableRepository->delete($id);
    }
}
